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
use App\Models\SuratJalanAttachment;
use App\Models\SuratJalanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                'dikirim' => $suratJalans->whereIn('status', ['DIKIRIM', 'MENUNGGU_DIKEMBALIKAN'])->count(),
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
            ? Gudang::query()
                ->where('id', '!=', $gudangId)
                ->where('kode', '!=', 'GDG-EXT')
                ->orderBy('nama')
                ->get()
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

        // Only show peminjaman that have been received (DITERIMA) and not yet returned
        $activePeminjamans = Schema::hasTable('peminjamans') && Schema::hasTable('peminjaman_items')
            ? Peminjaman::query()
                ->with(['items.item', 'gudangPemilik'])
                ->where('gudang_peminjam_id', $gudangId)
                ->where('status', 'DITERIMA')
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
            'gudang_tujuan_mode' => ['required', Rule::in(['existing', 'custom'])],
            'gudang_tujuan_id' => [
                'exclude_unless:gudang_tujuan_mode,existing',
                'required_if:gudang_tujuan_mode,existing',
                'integer',
                'exists:gudangs,id',
                'not_in:' . $gudangId,
            ],
            'gudang_custom_nama' => [
                'exclude_unless:gudang_tujuan_mode,custom',
                'required_if:gudang_tujuan_mode,custom',
                'string',
                'max:255',
            ],
            'gudang_custom_alamat' => [
                'exclude_unless:gudang_tujuan_mode,custom',
                'required_if:gudang_tujuan_mode,custom',
                'string',
                'max:255',
            ],
            'gudang_custom_telepon' => [
                'exclude_unless:gudang_tujuan_mode,custom',
                'required_if:gudang_tujuan_mode,custom',
                'string',
                'max:50',
            ],
            'pic_tujuan_id' => [
                'required',
                Rule::when(
                    $request->input('pic_tujuan_id') !== 'lainnya',
                    [
                        'integer',
                        Rule::exists('pics', 'id')->where(function ($query) use ($request) {
                            $gudangTujuan = $request->input('gudang_tujuan_id');
                            if ($gudangTujuan) {
                                $query->where('gudang_id', $gudangTujuan);
                            }
                        }),
                    ]
                ),
                Rule::when(
                    $request->input('pic_tujuan_id') === 'lainnya',
                    ['in:lainnya']
                ),
            ],
            'pic_custom_nama' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'required_if:pic_tujuan_id,lainnya',
                'string',
                'max:255',
            ],
            'pic_custom_jabatan' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'required_if:pic_tujuan_id,lainnya',
                'string',
                'max:255',
            ],
            'pic_custom_no_hp' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'required_if:pic_tujuan_id,lainnya',
                'string',
                'max:50',
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
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'items.*.item_id.exists' => 'Item harus berasal dari stok gudang Anda.',
            'gudang_tujuan_id.required_if' => 'Gudang tujuan wajib dipilih.',
            'gudang_custom_nama.required_if' => 'Nama gudang wajib diisi.',
            'gudang_custom_alamat.required_if' => 'Alamat gudang wajib diisi.',
            'gudang_custom_telepon.required_if' => 'No telp gudang wajib diisi.',
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
            'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang yang dipilih.',
            'pic_tujuan_id.integer' => 'PIC tujuan tidak valid.',
            'pic_custom_nama.required_if' => 'Nama PIC wajib diisi.',
            'pic_custom_jabatan.required_if' => 'Jabatan PIC wajib diisi.',
            'pic_custom_no_hp.required_if' => 'No HP PIC wajib diisi.',
        ]);

        $isCustomGudang = $validated['gudang_tujuan_mode'] === 'custom';
        $customGudangData = [
            'nama' => $validated['gudang_custom_nama'] ?? null,
            'alamat' => $validated['gudang_custom_alamat'] ?? null,
            'telepon' => $validated['gudang_custom_telepon'] ?? null,
        ];
        $gudangTujuanId = $isCustomGudang
            ? $this->resolveExternalGudangId()
            : (int) $validated['gudang_tujuan_id'];

        $picTujuanId = $validated['pic_tujuan_id'];
        if ($picTujuanId === 'lainnya') {
            $picTujuan = Pic::create([
                'nama' => $validated['pic_custom_nama'],
                'jabatan' => $validated['pic_custom_jabatan'],
                'no_hp' => $validated['pic_custom_no_hp'],
                'gudang_id' => $gudangTujuanId,
            ]);
            $picTujuanId = $picTujuan->id;
        }

        $warningItems = $this->buildStockWarnings($gudangId, $validated['items']);
        $tanggalKirim = Carbon::parse($validated['tanggal_kirim'])->startOfDay();
        $tanggalKembali = !empty($validated['tanggal_kembali']) ? Carbon::parse($validated['tanggal_kembali'])->startOfDay() : null;

        $suratJalanId = DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $tanggalKembali, $picTujuanId, $gudangTujuanId, $isCustomGudang, $customGudangData) {
            if ($validated['mode'] === 'transfer') {
                $suratJalan = SuratJalan::create([
                    'nomor' => $this->generateSuratJalanNomor($tanggalKirim),
                    'gudang_asal_id' => $gudangId,
                    'gudang_tujuan_id' => $gudangTujuanId,
                    'gudang_tujuan_is_custom' => $isCustomGudang,
                    'gudang_tujuan_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                    'gudang_tujuan_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                    'gudang_tujuan_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                    'pic_tujuan_id' => $picTujuanId,
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
                return $suratJalan->id;
            }

            // Generate nomor surat jalan terlebih dahulu agar bisa digunakan sebagai kode peminjaman
            $nomorSuratJalan = $this->generateSuratJalanNomor($tanggalKirim);

            $suratJalanKirim = SuratJalan::create([
                'nomor' => $nomorSuratJalan,
                'gudang_asal_id' => $gudangId,
                'gudang_tujuan_id' => $gudangTujuanId,
                'gudang_tujuan_is_custom' => $isCustomGudang,
                'gudang_tujuan_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                'gudang_tujuan_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                'gudang_tujuan_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                'pic_tujuan_id' => $picTujuanId,
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

            // Buat peminjaman dengan kode yang sama dengan nomor surat jalan
            $peminjaman = Peminjaman::create([
                'kode' => $nomorSuratJalan, // Gunakan nomor surat jalan sebagai kode peminjaman
                'gudang_peminjam_id' => $gudangTujuanId,
                'gudang_peminjam_is_custom' => $isCustomGudang,
                'gudang_peminjam_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                'gudang_peminjam_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                'gudang_peminjam_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                'gudang_pemilik_id' => $gudangId,
                'surat_jalan_kirim_id' => $suratJalanKirim->id,
                'status' => 'DIAJUKAN',
                'waktu_pengajuan' => now(),
                'durasi_hari' => $tanggalKembali ? $tanggalKirim->diffInDays($tanggalKembali) : null,
                'durasi_jam' => $tanggalKembali ? $tanggalKirim->diffInHours($tanggalKembali) : null,
                'batas_waktu_kembali' => $tanggalKembali,
                'catatan_pengiriman' => $validated['catatan'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->createSuratJalanItems($suratJalanKirim->id, $validated['items']);
            $this->createPeminjamanItems($peminjaman->id, $validated['items']);

            return $suratJalanKirim->id;
        });

        // Handle attachment upload
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($suratJalanId, $request->file('attachments'));
        }

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
                        ->where('status', 'DITERIMA')
                        ->whereNull('surat_jalan_kembali_id');
                }),
            ],
            'pic_tujuan_id' => ['required', 'integer', 'exists:pics,id'],
            'tanggal_kirim' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'nama_driver' => ['nullable', 'string', 'max:100'],
            'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
            'nomor_plat' => ['nullable', 'string', 'max:50'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
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

        $suratJalanId = DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $peminjaman) {
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

            // Only link the surat jalan to peminjaman, status change happens on approve
            $peminjaman->update([
                'surat_jalan_kembali_id' => $suratJalan->id,
            ]);

            return $suratJalan->id;
        });

        // Handle attachment upload
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($suratJalanId, $request->file('attachments'));
        }

        return redirect()
            ->route('gudang.surat-jalan.index')
            ->with('success', 'Draft pengembalian peminjaman berhasil dibuat.');
    }

    public function show($id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan', 'items.item', 'attachments'])
            ->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if ($gudangId && $suratJalan->gudang_asal_id !== $gudangId && $suratJalan->gudang_tujuan_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengakses surat jalan gudang lain.');
        }

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::with([
                'suratJalanKirim.gudangAsal',
                'suratJalanKirim.gudangTujuan',
                'suratJalanKirim.pembuat',
                'suratJalanKembali.gudangAsal',
                'suratJalanKembali.gudangTujuan',
                'suratJalanKembali.pembuat',
                'gudangPeminjam',
                'gudangPemilik',
            ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::with([
                'suratJalanKirim.gudangAsal',
                'suratJalanKirim.gudangTujuan',
                'suratJalanKirim.pembuat',
                'suratJalanKembali.gudangAsal',
                'suratJalanKembali.gudangTujuan',
                'suratJalanKembali.pembuat',
                'gudangPeminjam',
                'gudangPemilik',
            ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
        }

        return view('gudang.surat-jalan.show', compact('suratJalan', 'peminjaman'));
    }

    public function edit($id)
    {
        $suratJalan = SuratJalan::with(['items.item', 'gudangAsal', 'gudangTujuan', 'picTujuan', 'attachments'])
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
            ? Gudang::query()
                ->where('id', '!=', $gudangId)
                ->where('kode', '!=', 'GDG-EXT')
                ->orderBy('nama')
                ->get()
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
            'gudang_tujuan_mode' => ['required', Rule::in(['existing', 'custom'])],
            'gudang_tujuan_id' => [
                'exclude_unless:gudang_tujuan_mode,existing',
                'required_if:gudang_tujuan_mode,existing',
                'integer',
                'exists:gudangs,id',
                'not_in:' . $gudangId,
            ],
            'gudang_custom_nama' => [
                'exclude_unless:gudang_tujuan_mode,custom',
                'required_if:gudang_tujuan_mode,custom',
                'string',
                'max:255',
            ],
            'gudang_custom_alamat' => [
                'exclude_unless:gudang_tujuan_mode,custom',
                'required_if:gudang_tujuan_mode,custom',
                'string',
                'max:255',
            ],
            'gudang_custom_telepon' => [
                'exclude_unless:gudang_tujuan_mode,custom',
                'required_if:gudang_tujuan_mode,custom',
                'string',
                'max:50',
            ],
            'pic_tujuan_id' => [
                'required',
                Rule::when(
                    $request->input('pic_tujuan_id') !== 'lainnya',
                    [
                        'integer',
                        Rule::exists('pics', 'id')->where(function ($query) use ($request) {
                            $gudangTujuan = $request->input('gudang_tujuan_id');
                            if ($gudangTujuan) {
                                $query->where('gudang_id', $gudangTujuan);
                            }
                        }),
                    ]
                ),
                Rule::when(
                    $request->input('pic_tujuan_id') === 'lainnya',
                    ['in:lainnya']
                ),
            ],
            'pic_custom_nama' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'required_if:pic_tujuan_id,lainnya',
                'string',
                'max:255',
            ],
            'pic_custom_jabatan' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'required_if:pic_tujuan_id,lainnya',
                'string',
                'max:255',
            ],
            'pic_custom_no_hp' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'required_if:pic_tujuan_id,lainnya',
                'string',
                'max:50',
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
            'attachments' => ['nullable', 'array', 'max:' . (3 - $suratJalan->attachments()->count())],
            'attachments.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'items.*.item_id.exists' => 'Item harus berasal dari stok gudang Anda.',
            'gudang_tujuan_id.required_if' => 'Gudang tujuan wajib dipilih.',
            'gudang_custom_nama.required_if' => 'Nama gudang wajib diisi.',
            'gudang_custom_alamat.required_if' => 'Alamat gudang wajib diisi.',
            'gudang_custom_telepon.required_if' => 'No telp gudang wajib diisi.',
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
            'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang yang dipilih.',
            'attachments.max' => 'Maksimal 3 lampiran gambar per surat jalan.',
        ]);

        $warningItems = $this->buildStockWarnings($gudangId, $validated['items']);

        $isCustomGudang = $validated['gudang_tujuan_mode'] === 'custom';
        $customGudangData = [
            'nama' => $validated['gudang_custom_nama'] ?? null,
            'alamat' => $validated['gudang_custom_alamat'] ?? null,
            'telepon' => $validated['gudang_custom_telepon'] ?? null,
        ];
        $gudangTujuanId = $isCustomGudang
            ? $this->resolveExternalGudangId()
            : (int) $validated['gudang_tujuan_id'];

        $picTujuanId = $validated['pic_tujuan_id'];
        if ($picTujuanId === 'lainnya') {
            $picTujuan = Pic::create([
                'nama' => $validated['pic_custom_nama'],
                'jabatan' => $validated['pic_custom_jabatan'],
                'no_hp' => $validated['pic_custom_no_hp'],
                'gudang_id' => $gudangTujuanId,
            ]);
            $picTujuanId = $picTujuan->id;
        }

        DB::transaction(function () use ($suratJalan, $validated, $gudangId, $gudangTujuanId, $isCustomGudang, $customGudangData, $picTujuanId) {
            $suratJalan->update([
                'gudang_tujuan_id' => $gudangTujuanId,
                'gudang_tujuan_is_custom' => $isCustomGudang,
                'gudang_tujuan_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                'gudang_tujuan_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                'gudang_tujuan_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                'pic_tujuan_id' => $picTujuanId,
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
                        'gudang_peminjam_id' => $gudangTujuanId,
                        'gudang_peminjam_is_custom' => $isCustomGudang,
                        'gudang_peminjam_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                        'gudang_peminjam_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                        'gudang_peminjam_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                        'durasi_hari' => $tanggalKembali ? $tanggalKirim->diffInDays($tanggalKembali) : null,
                        'durasi_jam' => $tanggalKembali ? $tanggalKirim->diffInHours($tanggalKembali) : null,
                        'batas_waktu_kembali' => $tanggalKembali,
                        'catatan_pengiriman' => $validated['catatan'] ?? null,
                    ]);
                }
            }
        });

        // Handle attachment upload
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($suratJalan->id, $request->file('attachments'));
        }

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

        // Validasi attachment wajib minimal 1
        if ($suratJalan->attachments()->count() === 0) {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Wajib upload minimal 1 lampiran gambar sebelum mengirim surat jalan.');
        }

        try {
            DB::transaction(function () use ($suratJalan, $gudangId) {
                $isCustomGudang = (bool) $suratJalan->gudang_tujuan_is_custom;
                $gudangTujuanNama = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
                    : ($suratJalan->gudangTujuan->nama ?? 'Gudang Tujuan');

                $itemTotals = $suratJalan->items
                    ->groupBy('item_id')
                    ->map(fn ($rows) => $rows->sum('jumlah'));

                // Untuk PENGEMBALIAN, tidak perlu validasi stok (barang dikembalikan)
                if ($suratJalan->tipe !== 'PENGEMBALIAN') {
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
                            'keterangan' => "Pengiriman via {$suratJalan->nomor} ke {$gudangTujuanNama}"
                        ]);
                    }

                    if ($suratJalan->tipe === 'PEMINJAMAN' && $isCustomGudang) {
                        $suratJalan->update([
                            'status' => 'MENUNGGU_DIKEMBALIKAN',
                            'ttd_pembuat_id' => $suratJalan->ttd_pembuat_id ?? Auth::id(),
                            'waktu_ttd_pembuat' => $suratJalan->waktu_ttd_pembuat ?? now(),
                        ]);

                        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                        if ($peminjaman) {
                            $peminjaman->update([
                                'status' => 'MENUNGGU_DIKEMBALIKAN',
                                'waktu_kirim' => now(),
                            ]);
                        }
                    } else {
                        $suratJalan->update([
                            'status' => $isCustomGudang && $suratJalan->tipe === 'TRANSFER' ? 'SELESAI' : 'DIKIRIM',
                            'ttd_pembuat_id' => $suratJalan->ttd_pembuat_id ?? Auth::id(),
                            'waktu_ttd_pembuat' => $suratJalan->waktu_ttd_pembuat ?? now(),
                        ]);

                        // Update peminjaman status if applicable
                        if ($suratJalan->tipe === 'PEMINJAMAN') {
                            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                            if ($peminjaman) {
                                $peminjaman->update([
                                    'status' => 'DIKIRIM',
                                    'waktu_kirim' => now(),
                                ]);
                            }
                        }
                    }
                } else {
                    // PENGEMBALIAN: Update status to DIKEMBALIKAN
                    $suratJalan->update([
                        'status' => 'DIKEMBALIKAN',
                        'ttd_pembuat_id' => $suratJalan->ttd_pembuat_id ?? Auth::id(),
                        'waktu_ttd_pembuat' => $suratJalan->waktu_ttd_pembuat ?? now(),
                    ]);

                    // Update peminjaman status
                    $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                    if ($peminjaman) {
                        $peminjaman->update([
                            'status' => 'DIKEMBALIKAN',
                            'waktu_pengembalian' => now(),
                        ]);
                    }
                }
            });

            $message = $suratJalan->tipe === 'PENGEMBALIAN'
                ? 'Surat Jalan Pengembalian berhasil dikirim.'
                : ($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->gudang_tujuan_is_custom
                    ? 'Surat Jalan disetujui. Menunggu konfirmasi pengembalian.'
                    : 'Surat Jalan disetujui dan stok berhasil dikurangi.');

            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('success', $message);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Operator gudang tujuan menerima barang (DIPERIKSA -> DITERIMA)
     * Operator gudang pemilik menerima kembali barang pengembalian (DIPERIKSA -> SELESAI)
     */
    public function terima($id)
    {
        $suratJalan = SuratJalan::with('items.item')->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_tujuan_id !== $gudangId) {
            abort(403, 'Anda tidak berhak menerima surat jalan ini.');
        }

        if ($suratJalan->status !== 'DIPERIKSA') {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini belum diperiksa oleh security.');
        }

        try {
            DB::transaction(function () use ($suratJalan, $gudangId) {
                $itemTotals = $suratJalan->items
                    ->groupBy('item_id')
                    ->map(fn ($rows) => $rows->sum('jumlah'));

                if ($suratJalan->tipe === 'PENGEMBALIAN') {
                    // PENGEMBALIAN: Tambah stok ke gudang pemilik dan set status SELESAI
                    foreach ($itemTotals as $itemId => $qty) {
                        $stock = ItemStock::firstOrCreate(
                            ['gudang_id' => $gudangId, 'item_id' => $itemId],
                            ['jumlah' => 0, 'stok_minimum' => 0]
                        );

                        $stokSebelum = $stock->jumlah;
                        $stokSesudah = $stokSebelum + $qty;

                        $stock->increment('jumlah', $qty);

                        StockMovement::create([
                            'item_id' => $itemId,
                            'gudang_id' => $gudangId,
                            'tipe' => 'IN',
                            'jumlah' => $qty,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'referensi_type' => 'SuratJalan',
                            'referensi_id' => $suratJalan->id,
                            'created_by' => Auth::id(),
                            'keterangan' => "Pengembalian via {$suratJalan->nomor} dari {$suratJalan->gudangAsal->nama}"
                        ]);
                    }

                    $suratJalan->update([
                        'status' => 'SELESAI',
                        'ttd_penerima_id' => $suratJalan->ttd_penerima_id ?? Auth::id(),
                        'waktu_ttd_penerima' => $suratJalan->waktu_ttd_penerima ?? now(),
                    ]);

                    // Update peminjaman status to SELESAI
                    $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                    if ($peminjaman) {
                        $peminjaman->update([
                            'status' => 'SELESAI',
                            'waktu_selesai' => now(),
                        ]);

                        // Also update the original surat jalan kirim status to SELESAI
                        if ($peminjaman->surat_jalan_kirim_id) {
                            SuratJalan::where('id', $peminjaman->surat_jalan_kirim_id)
                                ->update(['status' => 'SELESAI']);
                        }
                    }
                } else {
                    // TRANSFER/PEMINJAMAN: Tambah stok ke gudang tujuan dan set status DITERIMA
                    foreach ($itemTotals as $itemId => $qty) {
                        $stock = ItemStock::firstOrCreate(
                            ['gudang_id' => $gudangId, 'item_id' => $itemId],
                            ['jumlah' => 0, 'stok_minimum' => 0]
                        );

                        $stokSebelum = $stock->jumlah;
                        $stokSesudah = $stokSebelum + $qty;

                        $stock->increment('jumlah', $qty);

                        StockMovement::create([
                            'item_id' => $itemId,
                            'gudang_id' => $gudangId,
                            'tipe' => 'IN',
                            'jumlah' => $qty,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'referensi_type' => 'SuratJalan',
                            'referensi_id' => $suratJalan->id,
                            'created_by' => Auth::id(),
                            'keterangan' => "Penerimaan via {$suratJalan->nomor} dari {$suratJalan->gudangAsal->nama}"
                        ]);
                    }

                    $suratJalan->update([
                        'status' => 'DITERIMA',
                        'ttd_penerima_id' => $suratJalan->ttd_penerima_id ?? Auth::id(),
                        'waktu_ttd_penerima' => $suratJalan->waktu_ttd_penerima ?? now(),
                    ]);

                    // Update peminjaman status if applicable
                    if ($suratJalan->tipe === 'PEMINJAMAN') {
                        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                        if ($peminjaman) {
                            $peminjaman->update([
                                'status' => 'DITERIMA',
                                'waktu_diterima' => now(),
                            ]);
                        }
                    } elseif ($suratJalan->tipe === 'TRANSFER') {
                        // For TRANSFER, mark as SELESAI after receiving
                        $suratJalan->update([
                            'status' => 'SELESAI',
                        ]);
                    }
                }
            });

            $message = $suratJalan->tipe === 'PENGEMBALIAN'
                ? 'Barang pengembalian berhasil diterima. Peminjaman selesai.'
                : 'Barang berhasil diterima dan stok telah ditambahkan.';

            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('success', $message);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', $e->getMessage());
        }
    }

    public function confirmReturnExternal($id)
    {
        $suratJalan = SuratJalan::with(['items.item', 'gudangAsal'])->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengonfirmasi pengembalian gudang lain.');
        }

        if ($suratJalan->tipe !== 'PEMINJAMAN' || !$suratJalan->gudang_tujuan_is_custom) {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini bukan peminjaman gudang eksternal.');
        }

        if ($suratJalan->status !== 'MENUNGGU_DIKEMBALIKAN') {
            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Status surat jalan tidak sesuai untuk konfirmasi pengembalian.');
        }

        try {
            DB::transaction(function () use ($suratJalan, $gudangId) {
                $itemTotals = $suratJalan->items
                    ->groupBy('item_id')
                    ->map(fn ($rows) => $rows->sum('jumlah'));

                foreach ($itemTotals as $itemId => $qty) {
                    $stock = ItemStock::firstOrCreate(
                        ['gudang_id' => $gudangId, 'item_id' => $itemId],
                        ['jumlah' => 0, 'stok_minimum' => 0]
                    );

                    $stokSebelum = $stock->jumlah;
                    $stokSesudah = $stokSebelum + $qty;

                    $stock->increment('jumlah', $qty);

                    $tujuanNama = $suratJalan->gudang_tujuan_custom_nama ?? 'Gudang eksternal';
                    StockMovement::create([
                        'item_id' => $itemId,
                        'gudang_id' => $gudangId,
                        'tipe' => 'IN',
                        'jumlah' => $qty,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'referensi_type' => 'SuratJalan',
                        'referensi_id' => $suratJalan->id,
                        'created_by' => Auth::id(),
                        'keterangan' => "Pengembalian manual dari {$tujuanNama}"
                    ]);
                }

                $suratJalan->update([
                    'status' => 'SELESAI',
                ]);

                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman) {
                    $now = now();
                    $peminjaman->update([
                        'status' => 'SELESAI',
                        'waktu_pengembalian' => $now,
                        'waktu_selesai' => $now,
                    ]);
                }
            });

            return redirect()
                ->route('gudang.surat-jalan.show', $suratJalan->id)
                ->with('success', 'Pengembalian barang telah dikonfirmasi.');
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
            if ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();

                if ($peminjaman) {
                    $peminjaman->update([
                        'surat_jalan_kembali_id' => null,
                        'status' => $this->resolvePeminjamanStatusAfterReturnDraftDelete($peminjaman),
                    ]);
                }
            } else {
                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman) {
                    $peminjaman->items()->delete();
                    $peminjaman->delete();
                }
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

    private function resolvePeminjamanStatusAfterReturnDraftDelete(Peminjaman $peminjaman): string
    {
        if ($peminjaman->waktu_diterima || $peminjaman->waktu_ttd_penerima) {
            return 'DITERIMA';
        }

        if ($peminjaman->waktu_kirim || $peminjaman->waktu_ttd_pengirim) {
            return 'DIKIRIM';
        }

        return 'DIAJUKAN';
    }

    private function generateSuratJalanNomor(Carbon $tanggal): string
    {
        do {
            $prefix = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $tanggalKode = $tanggal->format('ymd');
            $tahun = $tanggal->format('Y');
            $nomor = $prefix . '/SJ' . $tanggalKode . '/' . $tahun;
        } while (SuratJalan::where('nomor', $nomor)->exists());

        return $nomor;
    }

    private function generatePeminjamanKode(Carbon $tanggal): string
    {
        do {
            $prefix = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $tanggalKode = $tanggal->format('ymd');
            $tahun = $tanggal->format('Y');
            $kode = $prefix . '/SJ' . $tanggalKode . '/' . $tahun;
        } while (Peminjaman::where('kode', $kode)->exists());

        return $kode;
    }

    private function resolveExternalGudangId(): int
    {
        $gudang = Gudang::firstOrCreate(
            ['kode' => 'GDG-EXT'],
            [
                'nama' => 'Gudang Eksternal',
                'alamat' => '-',
                'telepon' => '-',
            ]
        );

        return $gudang->id;
    }

    /**
     * Generate PDF for existing Surat Jalan (download)
     */
    public function generatePdf(string $id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan'));
        $pdf->setPaper('A4', 'portrait');

        $safeNomor = str_replace(['/', '\\'], '-', $suratJalan->nomor);
        return $pdf->download('surat-jalan-' . $safeNomor . '.pdf');
    }

    /**
     * Preview PDF for existing Surat Jalan (inline display)
     */
    public function previewPdf(string $id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan'));
        $pdf->setPaper('A4', 'portrait');

        $safeNomor = str_replace(['/', '\\'], '-', $suratJalan->nomor);
        return $pdf->stream('surat-jalan-' . $safeNomor . '.pdf');
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

        $gudangMode = $request->input('gudang_tujuan_mode', 'existing');
        if ($gudangMode === 'custom') {
            $suratJalan->gudang_tujuan_is_custom = true;
            $suratJalan->gudang_tujuan_custom_nama = $request->input('gudang_custom_nama');
            $suratJalan->gudang_tujuan_custom_alamat = $request->input('gudang_custom_alamat');
            $suratJalan->gudang_tujuan_custom_telepon = $request->input('gudang_custom_telepon');
            $gudangTujuan = new Gudang([
                'nama' => $request->input('gudang_custom_nama'),
                'alamat' => $request->input('gudang_custom_alamat'),
                'telepon' => $request->input('gudang_custom_telepon'),
            ]);
        } else {
            $gudangTujuan = Gudang::find($request->input('gudang_tujuan_id'));
        }

        $suratJalan->setRelation('gudangTujuan', $gudangTujuan);

        $picInput = $request->input('pic_tujuan_id');
        if ($picInput === 'lainnya') {
            $picTujuan = new Pic([
                'nama' => $request->input('pic_custom_nama'),
                'jabatan' => $request->input('pic_custom_jabatan'),
                'no_hp' => $request->input('pic_custom_no_hp'),
                'gudang_id' => (int) $request->input('gudang_tujuan_id'),
            ]);
        } else {
            $picTujuan = Pic::find((int) $picInput);
        }

        $suratJalan->setRelation('picTujuan', $picTujuan);
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
        $suratJalan->setRelation('attachments', collect());

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('preview-surat-jalan.pdf');
    }

    /**
     * Store attachments for a surat jalan
     */
    private function storeAttachments(int $suratJalanId, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('surat-jalan-attachments', 'public');

            SuratJalanAttachment::create([
                'surat_jalan_id' => $suratJalanId,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    /**
     * Delete a single attachment
     */
    public function deleteAttachment($id)
    {
        $attachment = SuratJalanAttachment::findOrFail($id);
        $suratJalan = SuratJalan::findOrFail($attachment->surat_jalan_id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak menghapus lampiran ini.');
        }

        if ($suratJalan->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Lampiran hanya bisa dihapus saat status Draft.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
