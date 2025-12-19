<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Pic;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\StockMovement;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratJalanController extends Controller
{
    public function index(Request $request)
    {
        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $tab = $request->input('tab', 'keluar'); // Default to 'keluar'
        $filters = $request->only(['search', 'status', 'tipe', 'tanggal_mulai', 'tanggal_selesai', 'order_by']);
        $filters['tab'] = $tab;

        $suratJalans = $this->getSuratJalanListItems($filters, $gudangId);

        // Stats for current tab
        if ($tab === 'keluar') {
            $stats = [
                'total' => $suratJalans->count(),
                'draft' => $suratJalans->where('status', 'DRAFT')->count(),
                'dikirim' => $suratJalans->where('status', 'DIKIRIM')->count(),
                'diterima' => $suratJalans->where('status', 'DITERIMA')->count(),
                'selesai' => $suratJalans->where('status', 'SELESAI')->count(),
            ];
        } else {
            $stats = [
                'total' => $suratJalans->count(),
                'menunggu' => $suratJalans->where('status', 'DIKIRIM')->count(),
                'diterima' => $suratJalans->where('status', 'DITERIMA')->count(),
                'selesai' => $suratJalans->where('status', 'SELESAI')->count(),
            ];
        }

        // Count for tab badges
        $countKeluar = $this->countSuratKeluar($gudangId);
        $countMasuk = $this->countSuratMasuk($gudangId);

        $gudangs = Schema::hasTable('gudangs')
            ? Gudang::query()->where('id', '!=', $gudangId)->orderBy('nama')->get()
            : collect();

        $pics = Schema::hasTable('pics')
            ? Pic::query()->with('gudang')->orderBy('nama')->get()
            : collect();

        $availableStocks = Schema::hasTable('item_stocks')
            ? ItemStock::query()
                ->with('item')
                ->where('gudang_id', $gudangId)
                ->orderBy('item_id')
                ->get()
            : collect();

        $activePeminjamans = Schema::hasTable('peminjamans') && Schema::hasTable('peminjaman_items')
            ? Peminjaman::query()
                ->with(['items.item', 'gudangPemilik'])
                ->where('gudang_peminjam_id', $gudangId)
                ->whereIn('status', ['DIKIRIM', 'DITERIMA'])
                ->whereNull('surat_jalan_kembali_id')
                ->orderByDesc('waktu_pengajuan')
                ->get()
            : collect();

        return view('gudang.surat-jalan.index', compact('suratJalans', 'stats', 'gudangs', 'pics', 'availableStocks', 'filters', 'activePeminjamans', 'tab', 'countKeluar', 'countMasuk'));
    }

    public function create(Request $request)
    {
        return redirect()->route('gudang.surat-jalan.index');
    }

    public function store(Request $request)
    {
        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['transfer', 'peminjaman'])],
            'gudang_tujuan_id' => ['required', 'integer', 'exists:gudangs,id', 'not_in:' . $gudangId],
            'pic_tujuan_id' => [
                'required',
                'integer',
                Rule::exists('pics', 'id')->where(function ($query) use ($request) {
                    $gudangTujuan = $request->input('gudang_tujuan_id');
                    if ($gudangTujuan) {
                        $query->where('gudang_id', $gudangTujuan);
                    }
                }),
            ],
            'tanggal_kirim' => ['required', 'date'],
            'tanggal_kembali' => ['required_if:mode,peminjaman', 'nullable', 'date', 'after:tanggal_kirim'],
            'catatan' => ['nullable', 'string'],
            'nama_driver' => ['nullable', 'string', 'max:100'],
            'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
            'nomor_plat' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('item_stocks', 'item_id')->where(fn ($q) => $q->where('gudang_id', $gudangId)),
            ],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.keterangan' => ['nullable', 'string'],
        ], [
            'items.*.item_id.exists' => 'Item harus berasal dari stok gudang Anda.',
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
            'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang yang dipilih.',
            'pic_tujuan_id.integer' => 'PIC tujuan tidak valid.',
        ]);

        $warningItems = $this->buildStockWarnings($gudangId, $validated['items']);
        $tanggalKirim = Carbon::parse($validated['tanggal_kirim'])->startOfDay();
        $tanggalKembali = !empty($validated['tanggal_kembali']) ? Carbon::parse($validated['tanggal_kembali'])->startOfDay() : null;

        DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $tanggalKembali) {
            if ($validated['mode'] === 'transfer') {
                $suratJalan = SuratJalan::create([
                    'nomor' => $this->generateSuratJalanNomor($tanggalKirim),
                    'gudang_asal_id' => $gudangId,
                    'gudang_tujuan_id' => (int) $validated['gudang_tujuan_id'],
                    'pic_tujuan_id' => $validated['pic_tujuan_id'] ?? null,
                    'tipe' => 'TRANSFER',
                    'status' => 'DRAFT',
                    'tanggal' => $tanggalKirim->toDateString(),
                    'created_by' => Auth::id(),
                    'catatan' => $validated['catatan'] ?? null,
                    'nama_driver' => $validated['nama_driver'] ?? null,
                    'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                    'nomor_plat' => $validated['nomor_plat'] ?? null,
                    'pdf_path' => null,
                ]);

                $this->createSuratJalanItems($suratJalan->id, $validated['items']);
                return;
            }

            $peminjaman = Peminjaman::create([
                'kode' => $this->generatePeminjamanKode($tanggalKirim),
                'gudang_peminjam_id' => (int) $validated['gudang_tujuan_id'],
                'gudang_pemilik_id' => $gudangId,
                'status' => 'DIAJUKAN',
                'waktu_pengajuan' => now(),
                'durasi_hari' => $tanggalKembali ? $tanggalKirim->diffInDays($tanggalKembali) : null,
                'durasi_jam' => $tanggalKembali ? $tanggalKirim->diffInHours($tanggalKembali) : null,
                'waktu_pengembalian' => $tanggalKembali ? $tanggalKembali->toDateString() : null,
                'catatan_pengiriman' => $validated['catatan'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $suratJalanKirim = SuratJalan::create([
                'nomor' => $this->generateSuratJalanNomor($tanggalKirim),
                'gudang_asal_id' => $gudangId,
                'gudang_tujuan_id' => (int) $validated['gudang_tujuan_id'],
                'pic_tujuan_id' => $validated['pic_tujuan_id'] ?? null,
                'tipe' => 'PEMINJAMAN',
                'status' => 'DRAFT',
                'tanggal' => $tanggalKirim->toDateString(),
                'created_by' => Auth::id(),
                'catatan' => $validated['catatan'] ?? null,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
                'pdf_path' => null,
            ]);

            $peminjaman->update([
                'surat_jalan_kirim_id' => $suratJalanKirim->id,
            ]);

            $this->createSuratJalanItems($suratJalanKirim->id, $validated['items']);
            $this->createPeminjamanItems($peminjaman->id, $validated['items']);
        });

        $redirect = redirect()
            ->route('gudang.surat-jalan.index')
            ->with('success', 'Draft Surat Jalan berhasil dibuat.');

        if (!empty($warningItems)) {
            $redirect->with('warning', $warningItems);
        }

        return $redirect;
    }

    public function storeReturn(Request $request)
    {
        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $validated = $request->validate([
            'peminjaman_id' => [
                'required',
                'integer',
                Rule::exists('peminjamans', 'id')->where(function ($query) use ($gudangId) {
                    $query->where('gudang_peminjam_id', $gudangId)
                        ->whereIn('status', ['DIKIRIM', 'DITERIMA'])
                        ->whereNull('surat_jalan_kembali_id');
                }),
            ],
            'pic_tujuan_id' => ['required', 'integer', 'exists:pics,id'],
            'tanggal_kirim' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'nama_driver' => ['nullable', 'string', 'max:100'],
            'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
            'nomor_plat' => ['nullable', 'string', 'max:50'],
        ], [
            'peminjaman_id.required' => 'Kode peminjaman wajib dipilih.',
            'peminjaman_id.exists' => 'Kode peminjaman tidak valid atau sudah dikembalikan.',
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
        ]);

        $peminjaman = Peminjaman::with(['items', 'gudangPemilik'])
            ->where('id', $validated['peminjaman_id'])
            ->firstOrFail();

        $picValid = Pic::where('id', $validated['pic_tujuan_id'])
            ->where('gudang_id', $peminjaman->gudang_pemilik_id)
            ->exists();

        if (!$picValid) {
            return redirect()
                ->route('gudang.surat-jalan.index')
                ->withErrors(['pic_tujuan_id' => 'PIC tujuan tidak sesuai dengan gudang pemilik.'])
                ->withInput();
        }

        $tanggalKirim = Carbon::parse($validated['tanggal_kirim'])->startOfDay();

        DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $peminjaman) {
            $suratJalan = SuratJalan::create([
                'nomor' => $this->generateSuratJalanNomor($tanggalKirim),
                'gudang_asal_id' => $gudangId,
                'gudang_tujuan_id' => $peminjaman->gudang_pemilik_id,
                'pic_tujuan_id' => $validated['pic_tujuan_id'],
                'tipe' => 'PENGEMBALIAN',
                'status' => 'DRAFT',
                'tanggal' => $tanggalKirim->toDateString(),
                'created_by' => Auth::id(),
                'catatan' => $validated['catatan'] ?? null,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
                'pdf_path' => null,
            ]);

            $rows = $peminjaman->items->map(function ($item) use ($suratJalan) {
                return [
                    'surat_jalan_id' => $suratJalan->id,
                    'item_id' => $item->item_id,
                    'jumlah' => (int) $item->jumlah_dipinjam,
                    'keterangan' => 'Pengembalian barang peminjaman.',
                ];
            })->all();

            SuratJalanItem::insert($rows);

            $peminjaman->update([
                'surat_jalan_kembali_id' => $suratJalan->id,
                'status' => 'DIKEMBALIKAN',
                'waktu_pengembalian' => $tanggalKirim->toDateString(),
            ]);
        });

        return redirect()
            ->route('gudang.surat-jalan.index')
            ->with('success', 'Draft pengembalian peminjaman berhasil dibuat.');
    }

    public function show($id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan', 'items.item'])
            ->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if ($gudangId && $suratJalan->gudang_asal_id !== $gudangId && $suratJalan->gudang_tujuan_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengakses surat jalan gudang lain.');
        }

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        }

        return view('gudang.surat-jalan.show', compact('suratJalan', 'peminjaman'));
    }

    public function edit($id)
    {
        $suratJalan = SuratJalan::with(['items.item', 'gudangAsal', 'gudangTujuan', 'picTujuan'])
            ->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengedit surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'DRAFT') {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Hanya surat jalan Draft yang bisa diedit.');
        }

        $gudangs = Schema::hasTable('gudangs')
            ? Gudang::query()->where('id', '!=', $gudangId)->orderBy('nama')->get()
            : collect();

        $pics = Schema::hasTable('pics')
            ? Pic::query()->with('gudang')->orderBy('nama')->get()
            : collect();

        $availableStocks = Schema::hasTable('item_stocks')
            ? ItemStock::query()
                ->with('item')
                ->where('gudang_id', $gudangId)
                ->orderBy('item_id')
                ->get()
            : collect();

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        }

        return view('gudang.surat-jalan.edit', compact('suratJalan', 'gudangs', 'pics', 'availableStocks', 'peminjaman'));
    }

    public function update(Request $request, $id)
    {
        $suratJalan = SuratJalan::with('items')->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengedit surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'DRAFT') {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Hanya surat jalan Draft yang bisa diedit.');
        }

        if ($suratJalan->tipe === 'PENGEMBALIAN') {
            $validated = $request->validate([
                'pic_tujuan_id' => [
                    'required',
                    'integer',
                    Rule::exists('pics', 'id')->where(fn ($q) => $q->where('gudang_id', $suratJalan->gudang_tujuan_id)),
                ],
                'tanggal_kirim' => ['required', 'date'],
                'catatan' => ['nullable', 'string'],
                'nama_driver' => ['nullable', 'string', 'max:100'],
                'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
                'nomor_plat' => ['nullable', 'string', 'max:50'],
            ], [
                'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
                'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang tujuan.',
            ]);

            $suratJalan->update([
                'pic_tujuan_id' => $validated['pic_tujuan_id'],
                'tanggal' => Carbon::parse($validated['tanggal_kirim'])->toDateString(),
                'catatan' => $validated['catatan'] ?? null,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
            ]);

            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('success', 'Draft pengembalian berhasil diperbarui.');
        }

        $validated = $request->validate([
            'gudang_tujuan_id' => ['required', 'integer', 'exists:gudangs,id', 'not_in:' . $gudangId],
            'pic_tujuan_id' => [
                'required',
                'integer',
                Rule::exists('pics', 'id')->where(function ($query) use ($request) {
                    $gudangTujuan = $request->input('gudang_tujuan_id');
                    if ($gudangTujuan) {
                        $query->where('gudang_id', $gudangTujuan);
                    }
                }),
            ],
            'tanggal_kirim' => ['required', 'date'],
            'tanggal_kembali' => ['required_if:tipe,PEMINJAMAN', 'nullable', 'date', 'after:tanggal_kirim'],
            'catatan' => ['nullable', 'string'],
            'nama_driver' => ['nullable', 'string', 'max:100'],
            'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
            'nomor_plat' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('item_stocks', 'item_id')->where(fn ($q) => $q->where('gudang_id', $gudangId)),
            ],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.keterangan' => ['nullable', 'string'],
            'tipe' => ['required', Rule::in(['TRANSFER', 'PEMINJAMAN'])],
        ], [
            'items.*.item_id.exists' => 'Item harus berasal dari stok gudang Anda.',
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
            'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang yang dipilih.',
        ]);

        $warningItems = $this->buildStockWarnings($gudangId, $validated['items']);

        DB::transaction(function () use ($suratJalan, $validated, $gudangId) {
            $suratJalan->update([
                'gudang_tujuan_id' => (int) $validated['gudang_tujuan_id'],
                'pic_tujuan_id' => (int) $validated['pic_tujuan_id'],
                'tanggal' => Carbon::parse($validated['tanggal_kirim'])->toDateString(),
                'catatan' => $validated['catatan'] ?? null,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
            ]);

            $suratJalan->items()->delete();
            $this->createSuratJalanItems($suratJalan->id, $validated['items']);

            if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman) {
                    $tanggalKembali = !empty($validated['tanggal_kembali'])
                        ? Carbon::parse($validated['tanggal_kembali'])->startOfDay()
                        : null;
                    $tanggalKirim = Carbon::parse($validated['tanggal_kirim'])->startOfDay();

                    $peminjaman->update([
                        'gudang_peminjam_id' => (int) $validated['gudang_tujuan_id'],
                        'durasi_hari' => $tanggalKembali ? $tanggalKirim->diffInDays($tanggalKembali) : null,
                        'durasi_jam' => $tanggalKembali ? $tanggalKirim->diffInHours($tanggalKembali) : null,
                        'waktu_pengembalian' => $tanggalKembali?->toDateString(),
                        'catatan_pengiriman' => $validated['catatan'] ?? null,
                    ]);
                }
            }
        });

        $redirect = redirect()
            ->route('gudang.surat-jalan.show', $suratJalan->id)
            ->with('success', 'Draft surat jalan berhasil diperbarui.');

        if (!empty($warningItems)) {
            $redirect->with('warning', $warningItems);
        }

        return $redirect;
    }

    public function approve($id)
    {
        $suratJalan = SuratJalan::with('items.item')->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak menyetujui surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'DRAFT') {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini sudah diproses.');
        }

        try {
            DB::transaction(function () use ($suratJalan, $gudangId) {
                $itemTotals = $suratJalan->items
                    ->groupBy('item_id')
                    ->map(fn ($rows) => $rows->sum('jumlah'));

                // Validasi stok terlebih dahulu (per item total)
                foreach ($itemTotals as $itemId => $qty) {
                    $stock = ItemStock::where('gudang_id', $gudangId)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    $available = $stock?->jumlah ?? 0;
                    if ($available < $qty) {
                        $itemName = $suratJalan->items->firstWhere('item_id', $itemId)?->item->nama ?? "Item ID {$itemId}";
                        throw new \RuntimeException("Stok tidak cukup untuk {$itemName}.");
                    }
                }

                // Kurangi stok dan catat movement (per item total)
                foreach ($itemTotals as $itemId => $qty) {
                    $stock = ItemStock::where('gudang_id', $gudangId)
                        ->where('item_id', $itemId)
                        ->first();

                    $stokSebelum = $stock->jumlah;
                    $stokSesudah = $stokSebelum - $qty;

                    $stock->decrement('jumlah', $qty);

                    StockMovement::create([
                        'item_id' => $itemId,
                        'gudang_id' => $gudangId,
                        'tipe' => 'OUT',
                        'jumlah' => $qty,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'referensi_type' => 'SuratJalan',
                        'referensi_id' => $suratJalan->id,
                        'created_by' => Auth::id(),
                        'keterangan' => "Pengiriman via {$suratJalan->nomor} ke {$suratJalan->gudangTujuan->nama}"
                    ]);
                }

                $suratJalan->update([
                    'status' => 'DIKIRIM',
                ]);
            });

            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('success', 'Surat Jalan disetujui dan stok berhasil dikurangi.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $suratJalan = SuratJalan::with('items')->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak menghapus surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'DRAFT') {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Hanya surat jalan dengan status Draft yang bisa dihapus.');
        }

        DB::transaction(function () use ($suratJalan) {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)
                ->orWhere('surat_jalan_kembali_id', $suratJalan->id)
                ->first();

            if ($peminjaman) {
                $peminjaman->items()->delete();
                $peminjaman->delete();
            }

            $suratJalan->items()->delete();
            $suratJalan->delete();
        });

        return redirect()
            ->route('gudang.surat-jalan.index')
            ->with('success', 'Draft Surat Jalan berhasil dihapus.');
    }

    private function getSuratJalanListItems(array $filters = [], ?int $gudangId = null)
    {
        if (!Schema::hasTable('surat_jalans')) {
            return collect();
        }

        $gudangId = $gudangId ?? Auth::user()?->gudang_id;
        $tab = $filters['tab'] ?? 'keluar';
        $orderBy = $filters['order_by'] ?? 'terbaru';
        $direction = $orderBy === 'terlama' ? 'asc' : 'desc';

        $query = SuratJalan::query()
            ->with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan'])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->orderBy('tanggal', $direction)
            ->orderBy('id', $direction)
            ->limit(50);

        if ($gudangId) {
            if ($tab === 'keluar') {
                // Surat Keluar: Semua surat yang dibuat oleh gudang saya (gudang_asal_id = gudangId)
                $query->where('gudang_asal_id', $gudangId);
            } else {
                // Surat Masuk: Semua surat yang ditujukan ke gudang saya, tapi bukan DRAFT
                $query->where('gudang_tujuan_id', $gudangId)
                      ->where('status', '!=', 'DRAFT');
            }
        }

        if (!empty($filters['search'])) {
            $query->where('nomor', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }

        return $query->get();
    }

    private function countSuratKeluar(int $gudangId): array
    {
        $query = SuratJalan::where('gudang_asal_id', $gudangId);
        return [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'DRAFT')->count(),
        ];
    }

    private function countSuratMasuk(int $gudangId): array
    {
        $query = SuratJalan::where('gudang_tujuan_id', $gudangId)->where('status', '!=', 'DRAFT');
        return [
            'total' => (clone $query)->count(),
            'menunggu' => (clone $query)->where('status', 'DIKIRIM')->count(),
        ];
    }

    private function createSuratJalanItems(int $suratJalanId, array $items): void
    {
        $rows = collect($items)
            ->filter(fn ($row) => !empty($row['item_id']) && !empty($row['jumlah']))
            ->map(function ($row) use ($suratJalanId) {
                return [
                    'surat_jalan_id' => $suratJalanId,
                    'item_id' => (int) $row['item_id'],
                    'jumlah' => (int) $row['jumlah'],
                    'keterangan' => $row['keterangan'] ?? null,
                ];
            })
            ->values()
            ->all();

        SuratJalanItem::insert($rows);
    }

    private function createPeminjamanItems(int $peminjamanId, array $items): void
    {
        $rows = collect($items)
            ->filter(fn ($row) => !empty($row['item_id']) && !empty($row['jumlah']))
            ->map(function ($row) use ($peminjamanId) {
                return [
                    'peminjaman_id' => $peminjamanId,
                    'item_id' => (int) $row['item_id'],
                    'jumlah_dipinjam' => (int) $row['jumlah'],
                    'jumlah_diterima' => null,
                    'jumlah_dikembalikan' => null,
                    'kondisi_kembali' => null,
                    'catatan' => $row['keterangan'] ?? null,
                ];
            })
            ->values()
            ->all();

        PeminjamanItem::insert($rows);
    }

    private function buildStockWarnings(int $gudangId, array $items): array
    {
        $requested = collect($items)
            ->filter(fn ($row) => !empty($row['item_id']) && !empty($row['jumlah']))
            ->groupBy('item_id')
            ->map(fn ($rows) => $rows->sum(fn ($row) => (int) $row['jumlah']));

        if ($requested->isEmpty()) {
            return [];
        }

        $stocks = ItemStock::where('gudang_id', $gudangId)
            ->whereIn('item_id', $requested->keys())
            ->pluck('jumlah', 'item_id');

        $itemNames = Item::whereIn('id', $requested->keys())->pluck('nama', 'id');

        $warnings = [];
        foreach ($requested as $itemId => $qty) {
            $available = (int) ($stocks[$itemId] ?? 0);
            if ($qty > $available) {
                $name = $itemNames[$itemId] ?? 'Item';
                $warnings[] = "{$name} (diminta {$qty}, stok {$available})";
            }
        }

        return $warnings;
    }

    private function generateSuratJalanNomor(Carbon $tanggal): string
    {
        $prefix = 'SJ-' . $tanggal->format('Ymd') . '-';
        $latest = SuratJalan::query()
            ->where('nomor', 'like', $prefix . '%')
            ->orderByDesc('nomor')
            ->value('nomor');

        $nextNumber = 1;
        if ($latest) {
            $suffix = (int) substr($latest, -3);
            $nextNumber = $suffix + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function generatePeminjamanKode(Carbon $tanggal): string
    {
        $prefix = 'PMJ-' . $tanggal->format('Ymd') . '-';
        $latest = Peminjaman::query()
            ->where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $nextNumber = 1;
        if ($latest) {
            $suffix = (int) substr($latest, -3);
            $nextNumber = $suffix + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate PDF for existing Surat Jalan (download)
     */
    public function generatePdf(string $id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('surat-jalan-' . $suratJalan->nomor . '.pdf');
    }

    /**
     * Preview PDF for existing Surat Jalan (inline display)
     */
    public function previewPdf(string $id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('surat-jalan-' . $suratJalan->nomor . '.pdf');
    }

    /**
     * Preview PDF from form data (before saving draft)
     */
    public function previewDraft(Request $request)
    {
        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId) {
            return response()->json(['error' => 'User tidak memiliki gudang'], 403);
        }

        // Build temporary surat jalan object for preview
        $suratJalan = new SuratJalan();
        $suratJalan->nomor = 'DRAFT-PREVIEW';
        $suratJalan->tanggal = $request->input('tanggal_kirim') ? Carbon::parse($request->input('tanggal_kirim')) : now();
        $suratJalan->tipe = $request->input('mode') === 'peminjaman' ? 'PEMINJAMAN' : 'TRANSFER';
        $suratJalan->status = 'DRAFT';
        $suratJalan->catatan = $request->input('catatan');
        $suratJalan->nama_driver = $request->input('nama_driver');
        $suratJalan->jenis_kendaraan = $request->input('jenis_kendaraan');
        $suratJalan->nomor_plat = $request->input('nomor_plat');

        if ($request->input('tanggal_kembali')) {
            $suratJalan->tanggal_kembali = Carbon::parse($request->input('tanggal_kembali'));
        }

        // Load relations
        $suratJalan->setRelation('gudangAsal', Gudang::find($gudangId));
        $suratJalan->setRelation('gudangTujuan', Gudang::find($request->input('gudang_tujuan_id')));
        $suratJalan->setRelation('picTujuan', Pic::find($request->input('pic_tujuan_id')));
        $suratJalan->setRelation('pembuat', Auth::user());

        // Build items collection
        $items = collect($request->input('items', []))
            ->filter(fn($item) => !empty($item['item_id']) && !empty($item['jumlah']))
            ->map(function ($itemData) {
                $suratJalanItem = new SuratJalanItem();
                $suratJalanItem->jumlah = $itemData['jumlah'];
                $suratJalanItem->keterangan = $itemData['keterangan'] ?? null;
                $suratJalanItem->setRelation('item', Item::find($itemData['item_id']));
                return $suratJalanItem;
            });

        $suratJalan->setRelation('items', $items);

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('preview-surat-jalan.pdf');
    }
}
