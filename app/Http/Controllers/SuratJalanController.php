<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Pic;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SuratJalanController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'tipe', 'tanggal_mulai', 'tanggal_selesai', 'order_by']);
        $suratJalans = $this->getSuratJalanListItems($filters);

        $stats = [
            'total' => $suratJalans->count(),
            'draft' => $suratJalans->where('status', 'DRAFT')->count(),
            'dikirim' => $suratJalans->where('status', 'DIKIRIM')->count(),
            'diterima' => $suratJalans->where('status', 'DITERIMA')->count(),
            'dikembalikan' => $suratJalans->filter(function ($sj) {
                return $sj->tipe === 'PENGEMBALIAN' && $sj->status === 'DIKIRIM';
            })->count(),
            'selesai' => $suratJalans->where('status', 'SELESAI')->count(),
        ];

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
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

        return view('gudang.surat-jalan.index', compact('suratJalans', 'stats', 'gudangs', 'pics', 'availableStocks', 'filters'));
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
                foreach ($suratJalan->items as $item) {
                    $stock = ItemStock::where('gudang_id', $gudangId)
                        ->where('item_id', $item->item_id)
                        ->lockForUpdate()
                        ->first();

                    $available = $stock?->jumlah ?? 0;
                    if ($available < $item->jumlah) {
                        $itemName = $item->item->nama ?? "Item ID {$item->item_id}";
                        throw new \RuntimeException("Stok tidak cukup untuk {$itemName}.");
                    }
                }

                foreach ($suratJalan->items as $item) {
                    ItemStock::where('gudang_id', $gudangId)
                        ->where('item_id', $item->item_id)
                        ->decrement('jumlah', $item->jumlah);
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

    private function getSuratJalanListItems(array $filters = [])
    {
        if (!Schema::hasTable('surat_jalans')) {
            return collect();
        }

        $orderBy = $filters['order_by'] ?? 'terbaru';
        $direction = $orderBy === 'terlama' ? 'asc' : 'desc';

        $query = SuratJalan::query()
            ->with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan'])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->orderBy('tanggal', $direction)
            ->orderBy('id', $direction)
            ->limit(20);

        $gudangId = Auth::user()?->gudang_id;
        if ($gudangId) {
            $query->where(function ($q) use ($gudangId) {
                $q->where('gudang_asal_id', $gudangId)
                    ->orWhere('gudang_tujuan_id', $gudangId);
            });
        }

        if (!empty($filters['search'])) {
            $query->where('nomor', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'DIKEMBALIKAN') {
                $query->where('tipe', 'PENGEMBALIAN')->where('status', 'DIKIRIM');
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }

        return $query->get();
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
}
