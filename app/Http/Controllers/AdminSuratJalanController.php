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
use App\Models\SuratJalanStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SuratJalanExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminSuratJalanController extends Controller
{
    private const COMPANY_CODE = 'F2206040';

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';
        $isManager = $user?->role === 'manager';
        $gudangId = $user?->gudang_id;
        $adminFinish = $isAdmin && $request->boolean('admin_finish');
        $activeGudangId = $gudangId;
        $managedGudangIds = $isManager
            ? $user->managedGudangs()->pluck('gudangs.id')->all()
            : [];

        if ($isAdmin && !$gudangId) {
            if ($request->has('gudang_id')) {
                $activeGudangId = $request->input('gudang_id') ? (int) $request->input('gudang_id') : null;
                if ($activeGudangId) {
                    $request->session()->put('admin_surat_jalan_gudang_id', $activeGudangId);
                } else {
                    $request->session()->forget('admin_surat_jalan_gudang_id');
                }
            } else {
                $sessionGudangId = $request->session()->get('admin_surat_jalan_gudang_id');
                $activeGudangId = $sessionGudangId ? (int) $sessionGudangId : null;
            }
        }

        if ($isManager) {
            if (empty($managedGudangIds)) {
                abort(403, 'Manager belum memiliki gudang yang ditugaskan');
            }

            $requestedGudangId = $request->input('gudang_id');
            $requestedGudangId = $requestedGudangId ? (int) $requestedGudangId : null;
            if ($requestedGudangId && in_array($requestedGudangId, $managedGudangIds, true)) {
                $activeGudangId = $requestedGudangId;
            } else {
                $activeGudangId = $managedGudangIds[0] ?? null;
            }
        }

        if (!$activeGudangId && !$isAdmin) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $tab = $request->input('tab', 'keluar'); // Default to 'keluar'
        $filters = $request->only(['search', 'status', 'tipe', 'tanggal_mulai', 'tanggal_selesai', 'order_by']);
        $filters['tab'] = $tab;

        // Get paginated results
        $suratJalans = $this->getSuratJalanListItems($filters, $activeGudangId, true);

        // Stats for current tab (excluding SELESAI which is in riwayat)
        // Calculate stats using separate queries since we're now using pagination
        if ($tab === 'keluar' && $activeGudangId) {
            $baseQuery = SuratJalan::where('gudang_asal_id', $activeGudangId)->where('status', '!=', 'SELESAI');
            $stats = [
                'total' => (clone $baseQuery)->count(),
                'draft' => (clone $baseQuery)->whereIn('status', ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'])->count(),
                'dikirim' => (clone $baseQuery)->whereIn('status', ['DIKIRIM', 'DIPERIKSA_PENGIRIM', 'MENUNGGU_DIKEMBALIKAN'])->count(),
                'diterima' => (clone $baseQuery)->where('status', 'DITERIMA')->count(),
            ];
        } elseif ($activeGudangId) {
            $baseQuery = SuratJalan::where('gudang_tujuan_id', $activeGudangId)
                ->whereNotIn('status', ['DRAFT', 'SELESAI', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN']);
            $stats = [
                'total' => (clone $baseQuery)->count(),
                'menunggu' => (clone $baseQuery)->whereIn('status', ['DIKIRIM', 'DIKEMBALIKAN'])->count(),
                'diterima' => (clone $baseQuery)->where('status', 'DITERIMA')->count(),
            ];
        } else {
            $stats = ['total' => 0, 'draft' => 0, 'dikirim' => 0, 'diterima' => 0, 'menunggu' => 0];
        }

        // Count for tab badges
        $countKeluar = $activeGudangId ? $this->countSuratKeluar($activeGudangId) : ['total' => 0, 'draft' => 0];
        $countMasuk = $activeGudangId ? $this->countSuratMasuk($activeGudangId) : ['total' => 0, 'menunggu' => 0];

        $gudangs = Schema::hasTable('gudangs')
            ? tap(Gudang::query()->where('kode', '!=', 'GDG-EXT'), function ($query) use ($activeGudangId) {
                if ($activeGudangId) {
                    $query->where('id', '!=', $activeGudangId);
                }
            })
                ->orderBy('nama')
                ->get()
            : collect();

        $pics = Schema::hasTable('pics')
            ? Pic::query()->with('gudang')->orderBy('nama')->get()
            : collect();

        $adminUsers = Schema::hasTable('users')
            ? User::query()
                ->where(function ($query) {
                    $query->whereNotNull('gudang_id')
                        ->orWhere('role', 'manager');
                })
                ->with('managedGudangs:id')
                ->orderBy('name')
                ->get(['id', 'name', 'gudang_id', 'jabatan', 'role'])
            : collect();

        // Only show peminjaman that have been received and items are still with borrower
        // This includes: DITERIMA (received, no return), DIKEMBALIKAN (return in progress), DIPERIKSA (return being checked)
        // Excludes: SELESAI (returned), DITOLAK (rejected), DIAJUKAN/DIKIRIM (not yet received)
        $activePeminjamans = $activeGudangId && Schema::hasTable('peminjamans') && Schema::hasTable('peminjaman_items')
            ? Peminjaman::query()
                ->with(['items.item', 'gudangPemilik', 'suratJalanKirim'])
                ->where('gudang_peminjam_id', $activeGudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIPERIKSA'])
                ->orderByDesc('waktu_pengajuan')
                ->get()
            : collect();

        // Calculate borrowed items per item_id
        $borrowedItems = [];
        foreach ($activePeminjamans as $peminjaman) {
            foreach ($peminjaman->items as $peminjamanItem) {
                $itemId = $peminjamanItem->item_id;
                $jumlah = $peminjamanItem->jumlah_diterima ?? $peminjamanItem->jumlah_dipinjam;
                $borrowedItems[$itemId] = ($borrowedItems[$itemId] ?? 0) + $jumlah;
            }
        }

        // Get available stocks and subtract borrowed items, hide items with 0 own stock
        $availableStocks = Schema::hasTable('item_stocks') && $activeGudangId
            ? ItemStock::query()
                ->with('item')
                ->where('gudang_id', $activeGudangId)
                ->orderBy('item_id')
                ->get()
                ->map(function ($stock) use ($borrowedItems) {
                    // Subtract borrowed items from stock
                    $borrowed = $borrowedItems[$stock->item_id] ?? 0;
                    $stock->jumlah = max(0, $stock->jumlah - $borrowed);
                    return $stock;
                })
                ->filter(fn ($stock) => $stock->jumlah > 0)
                ->values()
            : collect();

        $selectionGudangs = collect();
        if (Schema::hasTable('gudangs')) {
            if ($isAdmin && !$gudangId) {
                $selectionGudangs = Gudang::query()->where('kode', '!=', 'GDG-EXT')->orderBy('nama')->get();
            } elseif ($isManager) {
                $selectionGudangs = Gudang::query()
                    ->where('kode', '!=', 'GDG-EXT')
                    ->whereIn('id', $managedGudangIds)
                    ->orderBy('nama')
                    ->get();
            }
        }
        $activeGudangName = $activeGudangId ? (Gudang::find($activeGudangId)?->nama ?? null) : null;

        return view('admin.surat-jalan.index', compact(
            'suratJalans',
            'stats',
            'gudangs',
            'pics',
            'adminUsers',
            'availableStocks',
            'filters',
            'activePeminjamans',
            'tab',
            'countKeluar',
            'countMasuk',
            'selectionGudangs',
            'activeGudangId',
            'activeGudangName'
        ));
    }

    public function create(Request $request)
    {
        return redirect()->route('admin.surat-jalan.index');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';
        if ($user?->role === 'manager') {
            abort(403, 'Manager tidak dapat membuat surat jalan.');
        }
        $gudangId = $user?->gudang_id;
        $adminFinish = $isAdmin && $request->boolean('admin_finish');
        $selectedGudangId = $gudangId ?: ($isAdmin ? (int) $request->input('gudang_asal_id') : null);
        $redirectParams = ['tab' => $request->input('tab', 'keluar')];
        if ($isAdmin && !$gudangId && $selectedGudangId) {
            $redirectParams['gudang_id'] = $selectedGudangId;
        }

        if ($isAdmin && !$adminFinish) {
            return redirect()
                ->back()
                ->with('error', 'Admin wajib menyelesaikan surat jalan saat membuat.')
                ->withInput();
        }

        if (!$selectedGudangId && !$isAdmin) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $validated = $request->validate([
            'gudang_asal_id' => [
                Rule::requiredIf($isAdmin && !$gudangId),
                'integer',
                'exists:gudangs,id',
            ],
            'ttd_pembuat_id' => ['nullable', 'integer', 'exists:users,id'],
            'mode' => ['required', Rule::in(['transfer', 'peminjaman'])],
            'gudang_tujuan_mode' => ['required', Rule::in(['existing', 'custom'])],
            'gudang_tujuan_id' => [
                'exclude_unless:gudang_tujuan_mode,existing',
                'required_if:gudang_tujuan_mode,existing',
                'integer',
                'exists:gudangs,id',
                'not_in:' . $selectedGudangId,
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
            'tanggal_kembali' => ['required_if:mode,peminjaman', 'nullable', 'date', 'after_or_equal:tanggal_kirim'],
            'catatan' => ['nullable', 'string'],
            'nama_driver' => ['required', 'string', 'max:100'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'nomor_plat' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('item_stocks', 'item_id')->where(fn ($q) => $q->where('gudang_id', $selectedGudangId)),
            ],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.keterangan' => ['nullable', 'string'],
            'attachments' => [
                Rule::requiredIf($isAdmin),
                'array',
                'max:3',
            ],
            'attachments.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            // Gudang Asal
            'gudang_asal_id.required' => 'Gudang asal wajib dipilih.',
            'gudang_asal_id.exists' => 'Gudang asal tidak valid.',
            'gudang_asal_id.integer' => 'Gudang asal tidak valid.',

            // Mode
            'mode.required' => 'Tipe surat jalan wajib dipilih.',
            'mode.in' => 'Tipe surat jalan tidak valid.',

            // Gudang Tujuan
            'gudang_tujuan_mode.required' => 'Mode gudang tujuan wajib dipilih.',
            'gudang_tujuan_mode.in' => 'Mode gudang tujuan tidak valid.',
            'gudang_tujuan_id.required_if' => 'Gudang tujuan wajib dipilih.',
            'gudang_tujuan_id.integer' => 'Gudang tujuan tidak valid.',
            'gudang_tujuan_id.exists' => 'Gudang tujuan tidak ditemukan.',
            'gudang_tujuan_id.not_in' => 'Gudang tujuan tidak boleh sama dengan gudang asal.',
            'gudang_custom_nama.required_if' => 'Nama gudang wajib diisi.',
            'gudang_custom_alamat.required_if' => 'Alamat gudang wajib diisi.',
            'gudang_custom_telepon.required_if' => 'No telp gudang wajib diisi.',

            // PIC Tujuan
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
            'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang yang dipilih.',
            'pic_tujuan_id.integer' => 'PIC tujuan tidak valid.',
            'pic_custom_nama.required_if' => 'Nama PIC wajib diisi.',
            'pic_custom_jabatan.required_if' => 'Jabatan PIC wajib diisi.',
            'pic_custom_no_hp.required_if' => 'No HP PIC wajib diisi.',

            // Tanggal
            'tanggal_kirim.required' => 'Tanggal kirim wajib diisi.',
            'tanggal_kirim.date' => 'Format tanggal kirim tidak valid.',
            'tanggal_kembali.required_if' => 'Tanggal kembali wajib diisi untuk peminjaman.',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali harus setelah atau sama dengan tanggal kirim.',

            // Driver & Kendaraan
            'nama_driver.required' => 'Nama driver wajib diisi.',
            'nama_driver.string' => 'Nama driver harus berupa teks.',
            'nama_driver.max' => 'Nama driver maksimal 100 karakter.',
            'jenis_kendaraan.required' => 'Jenis kendaraan wajib diisi.',
            'jenis_kendaraan.string' => 'Jenis kendaraan harus berupa teks.',
            'jenis_kendaraan.max' => 'Jenis kendaraan maksimal 100 karakter.',
            'nomor_plat.required' => 'Nomor plat wajib diisi.',
            'nomor_plat.string' => 'Nomor plat harus berupa teks.',
            'nomor_plat.max' => 'Nomor plat maksimal 50 karakter.',

            // Items
            'items.required' => 'Minimal harus ada 1 barang.',
            'items.array' => 'Format data barang tidak valid.',
            'items.min' => 'Minimal harus ada 1 barang.',
            'items.*.item_id.required' => 'Barang wajib dipilih.',
            'items.*.item_id.integer' => 'Barang tidak valid.',
            'items.*.item_id.exists' => 'Barang harus berasal dari stok gudang Anda.',
            'items.*.jumlah.required' => 'Jumlah barang wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah barang harus berupa angka.',
            'items.*.jumlah.min' => 'Jumlah barang minimal 1.',

            // Attachments
            'attachments.required' => 'Lampiran gambar wajib diupload.',
            'attachments.array' => 'Format lampiran tidak valid.',
            'attachments.max' => 'Maksimal 3 lampiran gambar.',
            'attachments.*.file' => 'Lampiran harus berupa file.',
            'attachments.*.image' => 'Lampiran harus berupa gambar.',
            'attachments.*.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'attachments.*.max' => 'Ukuran gambar maksimal 10MB.',
        ]);

        if (!$gudangId && $isAdmin) {
            $gudangId = (int) $validated['gudang_asal_id'];
        }

        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

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
        $picCustomData = null;
        if ($picTujuanId === 'lainnya') {
            $picCustomData = [
                'nama' => $validated['pic_custom_nama'],
                'jabatan' => $validated['pic_custom_jabatan'],
                'no_hp' => $validated['pic_custom_no_hp'],
            ];
            $picTujuanId = null;
        }

        $excludeBorrowed = in_array(($validated['mode'] ?? null), ['peminjaman', 'transfer'], true);
        $warningItems = $this->buildStockWarnings(
            $gudangId,
            $validated['items'],
            $excludeBorrowed
        );
        if (!empty($warningItems)) {
            $errorMessage = $this->buildStockErrorMessage(
                $gudangId,
                $validated['items'],
                $excludeBorrowed
            );
            return redirect()
                ->back()
                ->withErrors(['items' => $errorMessage])
                ->withInput();
        }
        $tanggalKirim = Carbon::parse($validated['tanggal_kirim'])->startOfDay();
        $tanggalKembali = !empty($validated['tanggal_kembali']) ? Carbon::parse($validated['tanggal_kembali'])->startOfDay() : null;
        $adminFinish = Auth::user()?->role === 'admin' && $request->boolean('admin_finish');
        $ttdPembuatId = $validated['ttd_pembuat_id'] ?? null;
        $ttdPenerimaId = null;
        $managerSignerId = null;

        if ($adminFinish) {
            $managerSignerId = $this->resolveManagerSignerId($gudangId);
            if (!$managerSignerId) {
                return redirect()
                    ->route('admin.surat-jalan.index', $redirectParams)
                    ->with('error', 'Manager pengirim belum ditetapkan untuk gudang ini.');
            }
            $ttdPembuatId = $managerSignerId;
        }

        if ($adminFinish) {
            try {
                $suratJalanId = $this->storeAdminCompletedSuratJalan(
                    $validated,
                    $gudangId,
                    $tanggalKirim,
                    $tanggalKembali,
                    $picTujuanId,
                    $gudangTujuanId,
                    $isCustomGudang,
                    $customGudangData,
                    $picCustomData,
                    $ttdPembuatId,
                    $ttdPenerimaId
                );
            } catch (\RuntimeException $e) {
                return redirect()
                    ->route('admin.surat-jalan.index')
                    ->with('error', $e->getMessage());
            }

            if ($request->hasFile('attachments')) {
                $this->storeAttachments($suratJalanId, $request->file('attachments'));
            }

            $this->seedAdminQuickStatusHistories(
                $suratJalanId,
                $validated['mode'],
                $isCustomGudang,
                $tanggalKirim,
                $tanggalKembali
            );

            $this->bumpSuratJalanCacheVersion([$gudangId, $gudangTujuanId]);
            $this->bumpSuratJalanDetailCacheVersion($suratJalanId);

            return redirect()
                ->route('admin.surat-jalan.show', $suratJalanId)
                ->with('success', 'Surat Jalan berhasil dibuat dan langsung diselesaikan.');
        }

        $suratJalanId = DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $tanggalKembali, $picTujuanId, $gudangTujuanId, $isCustomGudang, $customGudangData, $picCustomData) {
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
                    'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                    'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                    'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
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
                'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
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

        $this->bumpSuratJalanCacheVersion([$gudangId, $gudangTujuanId]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalanId);

        $redirect = redirect()
            ->route('admin.surat-jalan.index', $redirectParams)
            ->with('success', 'Draft Surat Jalan berhasil dibuat.');

        return $redirect;
    }

    public function storeReturn(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';
        if ($user?->role === 'manager') {
            abort(403, 'Manager tidak dapat membuat surat jalan.');
        }
        $gudangId = $user?->gudang_id;

        if (!$gudangId && !$isAdmin) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $adminFinish = $isAdmin && $request->boolean('admin_finish');

        $validated = $request->validate([
            'peminjaman_id' => [
                'required',
                'integer',
                Rule::exists('peminjamans', 'id')->where(function ($query) use ($gudangId) {
                    if ($gudangId) {
                        $query->where('gudang_peminjam_id', $gudangId);
                    }

                    $query->where('status', 'DITERIMA')
                        ->where(function ($subQuery) {
                            $subQuery->whereNull('surat_jalan_kembali_id')
                                ->orWhereIn('surat_jalan_kembali_id', function ($inner) {
                                    $inner->select('id')
                                        ->from('surat_jalans')
                                        ->where('status', 'DITOLAK')
                                        ->where('tipe', 'PENGEMBALIAN');
                                });
                        });
                }),
            ],
            'pic_tujuan_id' => [
                'required',
                Rule::when(
                    $request->input('pic_tujuan_id') !== 'lainnya',
                    ['integer', 'exists:pics,id']
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
                'nullable',
                'string',
                'max:255',
            ],
            'pic_custom_no_hp' => [
                'exclude_unless:pic_tujuan_id,lainnya',
                'nullable',
                'string',
                'max:50',
            ],
            'tanggal_kirim' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'nama_driver' => ['required', 'string', 'max:100'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'nomor_plat' => ['required', 'string', 'max:50'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'peminjaman_id.required' => 'Kode peminjaman wajib dipilih.',
            'peminjaman_id.exists' => 'Kode peminjaman tidak valid atau sudah dikembalikan.',
            'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
            'pic_custom_nama.required_if' => 'Nama PIC wajib diisi jika memilih Lainnya.',
            'nama_driver.required' => 'Nama driver wajib diisi.',
            'jenis_kendaraan.required' => 'Jenis kendaraan wajib diisi.',
            'nomor_plat.required' => 'Nomor plat wajib diisi.',
        ]);

        $peminjaman = Peminjaman::with(['items', 'gudangPemilik', 'suratJalanKirim.attachments'])
            ->where('id', $validated['peminjaman_id'])
            ->firstOrFail();

        if (!$gudangId) {
            $gudangId = $peminjaman->gudang_peminjam_id;
        }

        $managerSignerId = null;
        if ($adminFinish) {
            $managerSignerId = $this->resolveManagerSignerId($gudangId);
            if (!$managerSignerId) {
                return redirect()
                    ->route('admin.surat-jalan.index')
                    ->with('error', 'Manager pengirim belum ditetapkan untuk gudang ini.');
            }
        }

        // Handle PIC custom
        $picTujuanId = $validated['pic_tujuan_id'];
        $picCustomData = null;
        if ($picTujuanId === 'lainnya') {
            $picCustomData = [
                'nama' => $validated['pic_custom_nama'],
                'jabatan' => $validated['pic_custom_jabatan'] ?? null,
                'no_hp' => $validated['pic_custom_no_hp'] ?? null,
            ];
            $picTujuanId = null;
        } else {
            // Validate PIC exists in gudang pemilik
            $picValid = Pic::where('id', $picTujuanId)
                ->where('gudang_id', $peminjaman->gudang_pemilik_id)
                ->exists();

            if (!$picValid) {
                return redirect()
                    ->route('admin.surat-jalan.index')
                    ->withErrors(['pic_tujuan_id' => 'PIC tujuan tidak sesuai dengan gudang pemilik.'])
                    ->withInput();
            }
        }

        $tanggalKirim = Carbon::parse($validated['tanggal_kirim'])->startOfDay();

        $suratJalanId = DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $peminjaman, $adminFinish, $picTujuanId, $picCustomData, $managerSignerId) {
            $kembaliAt = $tanggalKirim->copy()->setTime(15, 0);
            $selesaiAt = $kembaliAt->copy()->addHour();

            $suratJalan = SuratJalan::create([
                'nomor' => $this->generateSuratJalanNomor($tanggalKirim),
                'gudang_asal_id' => $gudangId,
                'gudang_tujuan_id' => $peminjaman->gudang_pemilik_id,
                'pic_tujuan_id' => $picTujuanId,
                'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
                'tipe' => 'PENGEMBALIAN',
                'status' => $adminFinish ? 'SELESAI' : 'DRAFT',
                'tanggal' => $tanggalKirim->toDateString(),
                'created_by' => Auth::id(),
                'ttd_pembuat_id' => $adminFinish ? $managerSignerId : null,
                'waktu_ttd_pembuat' => $adminFinish ? $kembaliAt : null,
                'ttd_penerima_id' => null,
                'waktu_ttd_penerima' => $adminFinish ? $selesaiAt : null,
                'catatan' => $validated['catatan'] ?? null,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
                'pdf_path' => null,
                'created_at' => $adminFinish ? $kembaliAt : now(),
                'updated_at' => $adminFinish ? $selesaiAt : now(),
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
                'status' => $adminFinish ? 'SELESAI' : $peminjaman->status,
                'waktu_pengembalian' => $adminFinish ? $kembaliAt : $peminjaman->waktu_pengembalian,
                'waktu_selesai' => $adminFinish ? $selesaiAt : $peminjaman->waktu_selesai,
            ]);

            if ($adminFinish) {
                $itemTotals = $peminjaman->items
                    ->groupBy('item_id')
                    ->map(fn ($rows) => (int) $rows->sum('jumlah_dipinjam'));
                $gudangPemilikNama = $peminjaman->gudangPemilik->nama ?? 'Gudang Pemilik';

                $this->applyStockIn(
                    $peminjaman->gudang_pemilik_id,
                    $itemTotals,
                    $suratJalan,
                    $selesaiAt,
                    "Pengembalian via {$suratJalan->nomor} dari {$gudangPemilikNama}"
                );

                // Also update the original surat jalan kirim status to SELESAI
                if ($peminjaman->surat_jalan_kirim_id) {
                    $suratJalanKirim = SuratJalan::find($peminjaman->surat_jalan_kirim_id);
                    if ($suratJalanKirim) {
                        $suratJalanKirim->update(['status' => 'SELESAI']);
                    }
                }
            }

            return $suratJalan->id;
        });

        // Handle attachment upload
        if ($request->hasFile('attachments')) {
            // Jika ada upload baru, gunakan file yang diupload
            $this->storeAttachments($suratJalanId, $request->file('attachments'));
        } else {
            // Jika tidak ada upload, copy attachment dari surat jalan peminjaman awal
            $suratJalanAwal = $peminjaman->suratJalanKirim;
            if ($suratJalanAwal && $suratJalanAwal->attachments->isNotEmpty()) {
                $this->copyAttachmentsFromSuratJalan($suratJalanId, $suratJalanAwal);
            }
        }

        if ($adminFinish) {
            $this->seedAdminReturnStatusHistories($suratJalanId, $tanggalKirim);
        }

        $this->bumpSuratJalanCacheVersion([$gudangId, $peminjaman->gudang_pemilik_id]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalanId);
        if ($peminjaman->surat_jalan_kirim_id) {
            $this->bumpSuratJalanDetailCacheVersion($peminjaman->surat_jalan_kirim_id);
        }

        return redirect()
            ->route('admin.surat-jalan.index')
            ->with('success', $adminFinish ? 'Surat pengembalian berhasil dibuat dan langsung diselesaikan.' : 'Draft pengembalian peminjaman berhasil dibuat.');
    }

    public function show($id)
    {
        $cacheKey = $this->buildSuratJalanDetailCacheKey((int) $id);
        [$suratJalan, $peminjaman] = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($id) {
            $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan', 'items.item', 'attachments', 'statusHistories.actor'])
                ->findOrFail($id);

            $peminjaman = null;
            if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items.item',
                ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items.item',
                ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
            }

            return [$suratJalan, $peminjaman];
        });

        $user = Auth::user();
        $accessibleGudangIds = $this->resolveAccessibleGudangIds($user);
        if ($user?->role === 'manager' && empty($accessibleGudangIds)) {
            abort(403, 'Manager belum memiliki gudang yang ditugaskan');
        }
        if (!empty($accessibleGudangIds)
            && !in_array($suratJalan->gudang_asal_id, $accessibleGudangIds, true)
            && !in_array($suratJalan->gudang_tujuan_id, $accessibleGudangIds, true)
        ) {
            abort(403, 'Anda tidak berhak mengakses surat jalan gudang lain.');
        }

        $pics = collect();
        // Load PICs jika: (1) belum ada surat kembali, atau (2) surat kembali ditolak (buat ulang)
        $shouldLoadPics = $peminjaman && $peminjaman->status === 'DITERIMA' && (
            !$peminjaman->surat_jalan_kembali_id ||
            $peminjaman->suratJalanKembali?->status === 'DITOLAK'
        );
        if ($shouldLoadPics) {
            $pics = Schema::hasTable('pics')
                ? Pic::query()->where('gudang_id', $peminjaman->gudang_pemilik_id)->orderBy('nama')->get()
                : collect();
        }

        $isAdmin = $user?->role === 'admin';
        $isManager = $user?->role === 'manager';

        return view('admin.surat-jalan.show', compact('suratJalan', 'peminjaman', 'pics', 'isAdmin', 'isManager', 'accessibleGudangIds'));
    }

    public function edit($id)
    {
        $suratJalan = SuratJalan::with(['items.item', 'gudangAsal', 'gudangTujuan', 'picTujuan', 'attachments'])
            ->findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengedit surat jalan gudang lain.');
        }

        $editableStatuses = ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'];
        if (!in_array($suratJalan->status, $editableStatuses, true)) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Hanya surat jalan Draft atau Ditolak yang bisa diedit.');
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

        // Get active borrowed items for this gudang
        // Include peminjamans where items are still with borrower (DITERIMA, DIKEMBALIKAN, DIPERIKSA)
        $activePeminjamans = Schema::hasTable('peminjamans') && Schema::hasTable('peminjaman_items')
            ? Peminjaman::query()
                ->with('items')
                ->where('gudang_peminjam_id', $gudangId)
                ->whereIn('status', ['DITERIMA', 'DIKEMBALIKAN', 'DIPERIKSA'])
                ->get()
            : collect();

        $borrowedItems = [];
        foreach ($activePeminjamans as $peminjamanItem) {
            foreach ($peminjamanItem->items as $item) {
                $itemId = $item->item_id;
                $jumlah = $item->jumlah_diterima ?? $item->jumlah_dipinjam;
                $borrowedItems[$itemId] = ($borrowedItems[$itemId] ?? 0) + $jumlah;
            }
        }

        $availableStocks = Schema::hasTable('item_stocks')
            ? ItemStock::query()
                ->with('item')
                ->where('gudang_id', $gudangId)
                ->orderBy('item_id')
                ->get()
                ->map(function ($stock) use ($borrowedItems) {
                    $borrowed = $borrowedItems[$stock->item_id] ?? 0;
                    $stock->jumlah = max(0, $stock->jumlah - $borrowed);
                    return $stock;
                })
                ->filter(fn ($stock) => $stock->jumlah > 0)
                ->values()
            : collect();

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::with('gudangPemilik')
                ->where('surat_jalan_kembali_id', $suratJalan->id)
                ->first();
        }

        return view('admin.surat-jalan.edit', compact('suratJalan', 'gudangs', 'pics', 'availableStocks', 'peminjaman'));
    }

    public function update(Request $request, $id)
    {
        $suratJalan = SuratJalan::with('items')->findOrFail($id);
        $oldTujuanId = $suratJalan->gudang_tujuan_id;

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak mengedit surat jalan gudang lain.');
        }

        $editableStatuses = ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'];
        if (!in_array($suratJalan->status, $editableStatuses, true)) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Hanya surat jalan Draft atau Ditolak yang bisa diedit.');
        }

        $pendingDeleteCount = collect($request->input('delete_attachments', []))
            ->filter()
            ->unique()
            ->count();
        $maxAttachments = max(0, 3 - $suratJalan->attachments()->count() + $pendingDeleteCount);

        $resetAfterReject = $suratJalan->status === 'DITOLAK';
        $nextStatus = $resetAfterReject ? 'DRAFT' : $suratJalan->status;

        if ($suratJalan->tipe === 'PENGEMBALIAN') {
            $validated = $request->validate([
                'pic_tujuan_id' => [
                    'required',
                    Rule::when(
                        $request->input('pic_tujuan_id') !== 'lainnya',
                        [
                            'integer',
                            Rule::exists('pics', 'id')->where(fn ($q) => $q->where('gudang_id', $suratJalan->gudang_tujuan_id)),
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
                    'nullable',
                    'string',
                    'max:255',
                ],
                'pic_custom_no_hp' => [
                    'exclude_unless:pic_tujuan_id,lainnya',
                    'nullable',
                    'string',
                    'max:50',
                ],
                'tanggal_kirim' => ['required', 'date'],
                'catatan' => ['nullable', 'string'],
                'nama_driver' => ['nullable', 'string', 'max:100'],
                'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
                'nomor_plat' => ['nullable', 'string', 'max:50'],
                'attachments' => ['nullable', 'array', 'max:' . $maxAttachments],
                'attachments.*' => ['file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
                'delete_attachments' => ['nullable', 'array'],
                'delete_attachments.*' => [
                    'integer',
                    Rule::exists('surat_jalan_attachments', 'id')
                        ->where(fn ($query) => $query->where('surat_jalan_id', $suratJalan->id)),
                ],
            ], [
                'pic_tujuan_id.required' => 'PIC tujuan wajib dipilih.',
                'pic_tujuan_id.exists' => 'PIC tujuan tidak sesuai dengan gudang tujuan.',
                'pic_custom_nama.required_if' => 'Nama PIC wajib diisi jika memilih Lainnya.',
                'attachments.max' => 'Maksimal 3 lampiran gambar per surat jalan.',
            ]);

            $picTujuanId = $validated['pic_tujuan_id'];
            $picCustomData = null;
            if ($picTujuanId === 'lainnya') {
                $picCustomData = [
                    'nama' => $validated['pic_custom_nama'],
                    'jabatan' => $validated['pic_custom_jabatan'] ?? null,
                    'no_hp' => $validated['pic_custom_no_hp'] ?? null,
                ];
                $picTujuanId = null;
            }

            $catatanValue = $validated['catatan'] ?? null;
            if ($resetAfterReject) {
                $catatanValue = $this->stripSecurityRejectTags($catatanValue);
            }

            $updatePayload = [
                'pic_tujuan_id' => $picTujuanId,
                'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
                'tanggal' => Carbon::parse($validated['tanggal_kirim'])->toDateString(),
                'catatan' => $catatanValue,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
                'status' => $nextStatus,
            ];
            if ($resetAfterReject) {
                $updatePayload['ttd_pembuat_id'] = null;
                $updatePayload['waktu_ttd_pembuat'] = null;
            }

            $suratJalan->update($updatePayload);

            if ($resetAfterReject) {
                $this->resetSuratJalanAfterSecurityReject($suratJalan);
            }

            $this->deleteAttachmentsByIds($suratJalan, $request->input('delete_attachments', []));

            if ($request->hasFile('attachments')) {
                $this->storeAttachments($suratJalan->id, $request->file('attachments'));
            }

            $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $oldTujuanId, $suratJalan->gudang_tujuan_id]);
            $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
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
            'tanggal_kembali' => ['required_if:tipe,PEMINJAMAN', 'nullable', 'date', 'after_or_equal:tanggal_kirim'],
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
            'delete_attachments' => ['nullable', 'array'],
            'delete_attachments.*' => [
                'integer',
                Rule::exists('surat_jalan_attachments', 'id')
                    ->where(fn ($query) => $query->where('surat_jalan_id', $suratJalan->id)),
            ],
            'attachments' => ['nullable', 'array', 'max:' . $maxAttachments],
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

        $excludeBorrowed = in_array(($validated['tipe'] ?? null), ['PEMINJAMAN', 'TRANSFER'], true);
        $warningItems = $this->buildStockWarnings(
            $gudangId,
            $validated['items'],
            $excludeBorrowed
        );
        if (!empty($warningItems)) {
            $errorMessage = $this->buildStockErrorMessage(
                $gudangId,
                $validated['items'],
                $excludeBorrowed
            );
            return redirect()
                ->back()
                ->withErrors(['items' => $errorMessage])
                ->withInput();
        }

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
        $picCustomData = null;
        if ($picTujuanId === 'lainnya') {
            $picCustomData = [
                'nama' => $validated['pic_custom_nama'],
                'jabatan' => $validated['pic_custom_jabatan'],
                'no_hp' => $validated['pic_custom_no_hp'],
            ];
            $picTujuanId = null;
        }

        $catatanValue = $validated['catatan'] ?? null;
        if ($resetAfterReject) {
            $catatanValue = $this->stripSecurityRejectTags($catatanValue);
        }

        DB::transaction(function () use ($suratJalan, $validated, $gudangId, $gudangTujuanId, $isCustomGudang, $customGudangData, $picTujuanId, $picCustomData, $nextStatus, $resetAfterReject, $catatanValue) {
            $updatePayload = [
                'gudang_tujuan_id' => $gudangTujuanId,
                'gudang_tujuan_is_custom' => $isCustomGudang,
                'gudang_tujuan_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                'gudang_tujuan_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                'gudang_tujuan_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                'pic_tujuan_id' => $picTujuanId,
                'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
                'tanggal' => Carbon::parse($validated['tanggal_kirim'])->toDateString(),
                'catatan' => $catatanValue,
                'nama_driver' => $validated['nama_driver'] ?? null,
                'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                'nomor_plat' => $validated['nomor_plat'] ?? null,
                'status' => $nextStatus,
            ];
            if ($resetAfterReject) {
                $updatePayload['ttd_pembuat_id'] = null;
                $updatePayload['waktu_ttd_pembuat'] = null;
            }
            $suratJalan->update($updatePayload);

            $suratJalan->items()->delete();
            $this->createSuratJalanItems($suratJalan->id, $validated['items']);

            if ($resetAfterReject) {
                $this->resetSuratJalanAfterSecurityReject($suratJalan);
            }

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

        $this->deleteAttachmentsByIds($suratJalan, $request->input('delete_attachments', []));

        // Handle attachment upload
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($suratJalan->id, $request->file('attachments'));
        }

        $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $oldTujuanId, $suratJalan->gudang_tujuan_id]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        $redirect = redirect()
            ->route('admin.surat-jalan.show', $suratJalan->id)
            ->with('success', 'Draft surat jalan berhasil diperbarui.');

        return $redirect;
    }

    private function stripSecurityRejectTags(?string $catatan): ?string
    {
        if ($catatan === null) {
            return null;
        }

        $cleaned = preg_replace('/\\[DITOLAK_(PENGIRIM|PENERIMA):[^\\]]*\\]/', '', $catatan);
        $cleaned = trim((string) $cleaned);

        return $cleaned === '' ? null : $cleaned;
    }

    private function resetSuratJalanAfterSecurityReject(SuratJalan $suratJalan): void
    {
        SuratJalanItem::where('surat_jalan_id', $suratJalan->id)->update([
            'checked_by_security' => false,
            'checked_by_user_id' => null,
            'checked_at' => null,
        ]);

        if ($suratJalan->tipe !== 'PEMINJAMAN') {
            return;
        }

        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        if (!$peminjaman) {
            return;
        }

        $peminjaman->update([
            'status' => 'DIAJUKAN',
            'waktu_kirim' => null,
            'waktu_diterima' => null,
            'waktu_pengembalian' => null,
            'waktu_selesai' => null,
            'waktu_ttd_pengirim' => null,
            'waktu_ttd_penerima' => null,
            'waktu_ttd_pengembalian' => null,
            'waktu_ttd_terima_kembali' => null,
        ]);
    }

    public function requestApproval($id)
    {
        $suratJalan = SuratJalan::with(['attachments', 'items'])->findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';

        if ($user?->role === 'manager') {
            abort(403, 'Manager tidak dapat meminta persetujuan.');
        }

        $gudangId = $user?->gudang_id;
        if (!$isAdmin && (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId)) {
            abort(403, 'Anda tidak berhak meminta persetujuan surat jalan gudang lain.');
        }

        if (!in_array($suratJalan->status, ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'], true)) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini tidak dapat diajukan untuk persetujuan.');
        }

        $excludeBorrowed = in_array($suratJalan->tipe, ['PEMINJAMAN', 'TRANSFER'], true);
        $warningItems = $this->buildStockWarnings(
            $suratJalan->gudang_asal_id,
            $suratJalan->items
                ->map(fn ($item) => ['item_id' => $item->item_id, 'jumlah' => $item->jumlah])
                ->all(),
            $excludeBorrowed
        );
        if (!empty($warningItems)) {
            $errorMessage = $this->buildStockErrorMessage(
                $suratJalan->gudang_asal_id,
                $suratJalan->items
                    ->map(fn ($item) => ['item_id' => $item->item_id, 'jumlah' => $item->jumlah])
                    ->all(),
                $excludeBorrowed
            );
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', $errorMessage);
        }

        if ($suratJalan->attachments()->count() === 0) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Wajib upload minimal 1 lampiran gambar sebelum meminta persetujuan.');
        }

        $wasSecurityRejected = $suratJalan->status === 'DITOLAK';

        // Reset TTD, hash, dan catatan penolakan jika diajukan ulang dari status DITOLAK
        $updateData = ['status' => 'MENUNGGU_PERSETUJUAN'];
        if (in_array($suratJalan->status, ['DITOLAK', 'DITOLAK_PERSETUJUAN'], true)) {
            $updateData['catatan_penolakan'] = null;
            $updateData['ttd_pembuat_id'] = null;
            $updateData['waktu_ttd_pembuat'] = null;
            $updateData['signature_hash_pembuat'] = null;
            $updateData['signature_metadata_pembuat'] = null;
            $updateData['ttd_penerima_id'] = null;
            $updateData['waktu_ttd_penerima'] = null;
            $updateData['signature_hash_penerima'] = null;
            $updateData['signature_metadata_penerima'] = null;
        }
        if ($wasSecurityRejected) {
            $updateData['catatan'] = $this->stripSecurityRejectTags($suratJalan->catatan);
            $updateData['ttd_pembuat_id'] = null;
            $updateData['waktu_ttd_pembuat'] = null;
        }

        $suratJalan->update($updateData);
        if ($wasSecurityRejected) {
            $this->resetSuratJalanAfterSecurityReject($suratJalan);
        }

        $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        return redirect()
            ->route('admin.surat-jalan.show', $suratJalan->id)
            ->with('success', 'Surat Jalan berhasil diajukan untuk persetujuan.');
    }

    public function rejectApproval(Request $request, $id)
    {
        $suratJalan = SuratJalan::findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';
        $isManager = $user?->role === 'manager';
        $redirectRoute = $isManager ? 'manager.surat-jalan.show' : 'admin.surat-jalan.show';

        if (!$isAdmin && !$isManager) {
            abort(403, 'Anda tidak berhak menolak persetujuan.');
        }

        $accessibleGudangIds = $this->resolveAccessibleGudangIds($user);
        if ($isManager && empty($accessibleGudangIds)) {
            abort(403, 'Manager belum memiliki gudang yang ditugaskan');
        }
        if ($isManager && !in_array($suratJalan->gudang_asal_id, $accessibleGudangIds, true)) {
            abort(403, 'Anda tidak berhak menolak surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'MENUNGGU_PERSETUJUAN') {
            return redirect()
                ->route($redirectRoute, $suratJalan->id)
                ->with('error', 'Surat Jalan ini tidak dalam status menunggu persetujuan.');
        }

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
            'alasan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $alasan = trim((string) $validated['alasan']);
        $suratJalan->update([
            'status' => 'DITOLAK_PERSETUJUAN',
            'catatan_penolakan' => "[DITOLAK PERSETUJUAN: {$alasan}]",
        ]);

        $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        return redirect()
            ->route($redirectRoute, $suratJalan->id)
            ->with('success', 'Persetujuan surat jalan ditolak. Silakan perbaiki dan ajukan ulang.');
    }

    public function approve($id)
    {
        $suratJalan = SuratJalan::with('items.item')->findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';
        $isManager = $user?->role === 'manager';
        $redirectRoute = $isManager ? 'manager.surat-jalan.show' : 'admin.surat-jalan.show';

        if (!$isAdmin && !$isManager) {
            abort(403, 'Anda tidak berhak menyetujui surat jalan.');
        }

        $accessibleGudangIds = $this->resolveAccessibleGudangIds($user);
        if ($isManager && empty($accessibleGudangIds)) {
            abort(403, 'Manager belum memiliki gudang yang ditugaskan');
        }
        if ($isManager && !in_array($suratJalan->gudang_asal_id, $accessibleGudangIds, true)) {
            abort(403, 'Anda tidak berhak menyetujui surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'MENUNGGU_PERSETUJUAN') {
            return redirect()
                ->route($redirectRoute, $suratJalan->id)
                ->with('error', 'Surat Jalan ini tidak dalam status menunggu persetujuan.');
        }

        if ($suratJalan->attachments()->count() === 0) {
            return redirect()
                ->route($redirectRoute, $suratJalan->id)
                ->with('error', 'Lampiran wajib ada sebelum menyetujui surat jalan.');
        }

        $managerSignerId = $this->resolveManagerSignerId($suratJalan->gudang_asal_id);
        if (!$managerSignerId) {
            return redirect()
                ->route($redirectRoute, $suratJalan->id)
                ->with('error', 'Manager pengirim belum ditetapkan untuk gudang ini.');
        }

        try {
            DB::transaction(function () use ($suratJalan, $user, $managerSignerId) {
                $isCustomGudang = (bool) $suratJalan->gudang_tujuan_is_custom;
                $gudangTujuanNama = $suratJalan->gudang_tujuan_is_custom
                    ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
                    : ($suratJalan->gudangTujuan->nama ?? 'Gudang Tujuan');

                $itemTotals = $suratJalan->items
                    ->groupBy('item_id')
                    ->map(fn ($rows) => $rows->sum('jumlah'));

                // Untuk PENGEMBALIAN, tidak perlu validasi stok (barang dikembalikan)
                if ($suratJalan->tipe !== 'PENGEMBALIAN') {
                    $gudangId = $suratJalan->gudang_asal_id;
                    $borrowedTotals = $suratJalan->tipe === 'PEMINJAMAN'
                        ? $this->getBorrowedItemTotals($gudangId, $itemTotals->keys())
                        : collect();

                    // Validasi stok terlebih dahulu (per item total)
                    foreach ($itemTotals as $itemId => $qty) {
                        $stock = ItemStock::where('gudang_id', $gudangId)
                            ->where('item_id', $itemId)
                            ->lockForUpdate()
                            ->first();

                        $available = $stock?->jumlah ?? 0;
                        if ($suratJalan->tipe === 'PEMINJAMAN') {
                            $available -= (int) ($borrowedTotals[$itemId] ?? 0);
                            $available = max(0, $available);
                        }
                        if ($available < $qty) {
                            $itemName = $suratJalan->items->firstWhere('item_id', $itemId)?->item->nama ?? "Item ID {$itemId}";
                            $detail = $suratJalan->tipe === 'PEMINJAMAN'
                                ? "Stok sendiri tidak cukup untuk {$itemName} (dibutuhkan {$qty}, tersedia {$available}). Barang pinjaman dari gudang lain tidak dapat dipinjamkan."
                                : "Stok tidak cukup untuk {$itemName}.";
                            throw new \RuntimeException($detail);
                        }
                    }

                    // Kurangi stok dan catat movement (per item total)
                    $movementUserId = $suratJalan->created_by ?: Auth::id();
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
                            'created_by' => $movementUserId,
                            'keterangan' => "Pengiriman via {$suratJalan->nomor} ke {$gudangTujuanNama}"
                        ]);
                    }

                    $suratJalan->update([
                        'status' => 'DIPERIKSA_PENGIRIM',
                        'ttd_pembuat_id' => $suratJalan->ttd_pembuat_id ?? $managerSignerId,
                        'waktu_ttd_pembuat' => $suratJalan->waktu_ttd_pembuat ?? now(),
                    ]);

                    // Generate dan simpan hash signature pembuat untuk integritas dokumen
                    $suratJalan->refresh();
                    $suratJalan->update([
                        'signature_hash_pembuat' => $suratJalan->generateDocumentHash('pembuat'),
                        'signature_metadata_pembuat' => SuratJalan::generateSignatureMetadata(),
                    ]);
                } else {
                    // PENGEMBALIAN: kurangi stok dari gudang peminjam (gudang_asal) dan menunggu pemeriksaan security pengirim
                    $gudangId = $suratJalan->gudang_asal_id;
                    $movementUserId = $suratJalan->created_by ?: Auth::id();

                    // Kurangi stok dan catat movement (per item total)
                    foreach ($itemTotals as $itemId => $qty) {
                        $stock = ItemStock::where('gudang_id', $gudangId)
                            ->where('item_id', $itemId)
                            ->first();

                        if ($stock) {
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
                                'created_by' => $movementUserId,
                                'keterangan' => "Pengembalian via {$suratJalan->nomor} ke {$gudangTujuanNama}"
                            ]);
                        }
                    }

                    $suratJalan->update([
                        'status' => 'DIPERIKSA_PENGIRIM',
                        'ttd_pembuat_id' => $suratJalan->ttd_pembuat_id ?? $managerSignerId,
                        'waktu_ttd_pembuat' => $suratJalan->waktu_ttd_pembuat ?? now(),
                    ]);

                    // Generate dan simpan hash signature pembuat untuk integritas dokumen
                    $suratJalan->refresh();
                    $suratJalan->update([
                        'signature_hash_pembuat' => $suratJalan->generateDocumentHash('pembuat'),
                        'signature_metadata_pembuat' => SuratJalan::generateSignatureMetadata(),
                    ]);
                }
            });

            $message = $suratJalan->tipe === 'PENGEMBALIAN'
                ? 'Surat Jalan Pengembalian disetujui. Menunggu pemeriksaan security pengirim.'
                : 'Surat Jalan disetujui. Menunggu pemeriksaan security pengirim.';

            $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
            $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

            // Bump cache untuk surat peminjaman juga jika PENGEMBALIAN
            if ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman?->surat_jalan_kirim_id) {
                    $this->bumpSuratJalanDetailCacheVersion($peminjaman->surat_jalan_kirim_id);
                }
            }

            return redirect()
                ->route($redirectRoute, $suratJalan->id)
                ->with('success', $message);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route($redirectRoute, $suratJalan->id)
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

        if (!in_array($suratJalan->status, ['DIPERIKSA', 'DIPERIKSA_PENERIMA'], true)) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini belum diperiksa oleh security penerima.');
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

                    // Generate dan simpan hash signature penerima untuk integritas dokumen
                    $suratJalan->refresh();
                    $suratJalan->update([
                        'signature_hash_penerima' => $suratJalan->generateDocumentHash('penerima'),
                        'signature_metadata_penerima' => SuratJalan::generateSignatureMetadata(),
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
                            $suratJalanKirim = SuratJalan::find($peminjaman->surat_jalan_kirim_id);
                            if ($suratJalanKirim) {
                                $suratJalanKirim->update(['status' => 'SELESAI']);
                            }
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

                    // Generate dan simpan hash signature penerima untuk integritas dokumen
                    $suratJalan->refresh();
                    $suratJalan->update([
                        'signature_hash_penerima' => $suratJalan->generateDocumentHash('penerima'),
                        'signature_metadata_penerima' => SuratJalan::generateSignatureMetadata(),
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

            $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
            $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

            // Bump cache untuk surat peminjaman juga jika PENGEMBALIAN
            if ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman?->surat_jalan_kirim_id) {
                    $this->bumpSuratJalanDetailCacheVersion($peminjaman->surat_jalan_kirim_id);
                }
            }

            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('success', $message);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', $e->getMessage());
        }
    }

    public function confirmReturnExternal($id)
    {
        $suratJalan = SuratJalan::with(['items.item', 'gudangAsal'])->findOrFail($id);

        $user = Auth::user();
        $isAdmin = $user?->role === 'admin';
        $gudangId = $isAdmin ? $suratJalan->gudang_asal_id : ($user?->gudang_id ?? null);
        if (!$isAdmin && (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId)) {
            abort(403, 'Anda tidak berhak mengonfirmasi pengembalian gudang lain.');
        }

        if ($suratJalan->tipe !== 'PEMINJAMAN' || !$suratJalan->gudang_tujuan_is_custom) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini bukan peminjaman gudang eksternal.');
        }

        if ($suratJalan->status !== 'MENUNGGU_DIKEMBALIKAN') {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
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

            $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
            $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('success', 'Pengembalian barang telah dikonfirmasi.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', $e->getMessage());
        }
    }

    public function finalizeRejected($id)
    {
        $suratJalan = SuratJalan::findOrFail($id);

        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId || $suratJalan->gudang_asal_id !== $gudangId) {
            abort(403, 'Anda tidak berhak menyelesaikan surat jalan gudang lain.');
        }

        if ($suratJalan->status !== 'DITOLAK') {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat Jalan ini belum berstatus Ditolak.');
        }

        return redirect()
            ->route('admin.surat-jalan.show', $suratJalan->id)
            ->with('error', 'Surat Jalan ditolak. Status tetap DITOLAK.');
    }

    public function destroy($id)
    {
        $suratJalan = SuratJalan::with('items')->findOrFail($id);

        // Admin bisa membatalkan semua surat jalan kecuali yang sudah SELESAI
        if ($suratJalan->status === 'SELESAI') {
            return redirect()
                ->route('admin.surat-jalan.show', $suratJalan->id)
                ->with('error', 'Surat jalan yang sudah SELESAI tidak dapat dibatalkan.');
        }

        $nomorSuratJalan = $suratJalan->nomor;
        $hasStockMovements = $this->hasStockMovements($suratJalan->id);

        DB::transaction(function () use ($suratJalan, $hasStockMovements) {
            // Rollback stock movements jika ada
            if ($hasStockMovements) {
                $this->reverseStockMovements($suratJalan);
            }

            // Handle peminjaman records
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

            // Delete attachments if any
            foreach ($suratJalan->attachments ?? [] as $attachment) {
                if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            $suratJalan->items()->delete();
            $suratJalan->delete();
        });

        $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        $message = $hasStockMovements
            ? "Surat Jalan {$nomorSuratJalan} berhasil dibatalkan dan stok telah dikembalikan."
            : "Surat Jalan {$nomorSuratJalan} berhasil dihapus.";

        return redirect()
            ->route('admin.surat-jalan.index')
            ->with('success', $message);
    }

    /**
     * Check if surat jalan has any stock movements
     */
    private function hasStockMovements(int $suratJalanId): bool
    {
        return StockMovement::where('referensi_type', 'SuratJalan')
            ->where('referensi_id', $suratJalanId)
            ->exists();
    }

    /**
     * Reverse all stock movements for a surat jalan
     */
    private function reverseStockMovements(SuratJalan $suratJalan): void
    {
        $movements = StockMovement::where('referensi_type', 'SuratJalan')
            ->where('referensi_id', $suratJalan->id)
            ->get();

        $now = now();

        foreach ($movements as $movement) {
            $stock = ItemStock::where('gudang_id', $movement->gudang_id)
                ->where('item_id', $movement->item_id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                // Create stock record if it doesn't exist (edge case)
                $stock = ItemStock::create([
                    'gudang_id' => $movement->gudang_id,
                    'item_id' => $movement->item_id,
                    'jumlah' => 0,
                    'stok_minimum' => 0,
                ]);
            }

            $stokSebelum = $stock->jumlah;

            if ($movement->tipe === 'OUT') {
                // Reverse OUT: add back to stock
                $stock->increment('jumlah', $movement->jumlah);
                $reverseTipe = 'IN';
            } else {
                // Reverse IN: remove from stock
                $stock->decrement('jumlah', $movement->jumlah);
                $reverseTipe = 'OUT';
            }

            $stokSesudah = $stock->fresh()->jumlah;

            // Create reverse movement for audit trail
            StockMovement::create([
                'item_id' => $movement->item_id,
                'gudang_id' => $movement->gudang_id,
                'tipe' => $reverseTipe,
                'jumlah' => $movement->jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi_type' => 'SuratJalan',
                'referensi_id' => $suratJalan->id,
                'created_by' => Auth::id(),
                'keterangan' => "ROLLBACK: Pembatalan Surat Jalan {$suratJalan->nomor}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function getSuratJalanListItems(array $filters = [], ?int $gudangId = null, bool $paginate = false)
    {
        if (!Schema::hasTable('surat_jalans')) {
            return $paginate ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) : collect();
        }

        $gudangId = $gudangId ?? Auth::user()?->gudang_id;
        $tab = $filters['tab'] ?? 'keluar';
        $orderBy = $filters['order_by'] ?? 'terbaru';
        $direction = $orderBy === 'terlama' ? 'asc' : 'desc';

        $query = SuratJalan::query()
            ->with([
                'gudangAsal',
                'gudangTujuan',
                'pembuat',
                'picTujuan',
                'peminjaman.suratJalanKembali:id,nomor',  // Untuk PEMINJAMAN: cek apakah sudah ada pengembalian
                'peminjamanKembali.suratJalanKirim:id,nomor',  // Untuk PENGEMBALIAN: ambil surat peminjaman asal
            ])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->orderBy('tanggal', $direction)
            ->orderBy('id', $direction);

        if ($gudangId) {
            if ($tab === 'keluar') {
                // Surat Keluar: Semua surat yang dibuat oleh gudang saya (gudang_asal_id = gudangId)
                $query->where('gudang_asal_id', $gudangId);
            } else {
                // Surat Masuk: Semua surat yang ditujukan ke gudang saya, exclude DRAFT dan status persetujuan
                $query->where('gudang_tujuan_id', $gudangId)
                    ->whereNotIn('status', ['DRAFT', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN']);
            }
        }

        $isAdmin = Auth::user()?->role === 'admin';

        if (!empty($filters['search'])) {
            $searchLower = strtolower($filters['search']);
            $query->whereRaw('LOWER(nomor) LIKE ?', ['%' . $searchLower . '%']);
        }

        if (!empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (!empty($filters['status'])) {
            $statusFilter = $filters['status'];
            $statusGroups = [
                'DIPERIKSA' => ['DIPERIKSA', 'DIPERIKSA_PENGIRIM', 'DIPERIKSA_PENERIMA'],
                'DITERIMA' => ['DITERIMA', 'MENUNGGU_DIKEMBALIKAN'],
                'DITOLAK' => ['DITOLAK', 'DITOLAK_PERSETUJUAN'],
            ];
            if (array_key_exists($statusFilter, $statusGroups)) {
                $query->whereIn('status', $statusGroups[$statusFilter]);
            } else {
                $query->where('status', $statusFilter);
            }
        } elseif (!$isAdmin) {
            $query->where('status', '!=', 'SELESAI');
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }

        if ($paginate) {
            return $query->paginate(15)->onEachSide(1)->withQueryString();
        }

        return $query->limit(50)->get();
    }

    private function countSuratKeluar(int $gudangId): array
    {
        $cacheKey = $this->buildSuratJalanCacheKey('count_keluar', $gudangId, []);
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($gudangId) {
            // Exclude SELESAI from counts (moved to riwayat)
            $query = SuratJalan::where('gudang_asal_id', $gudangId)->where('status', '!=', 'SELESAI');
            return [
                'total' => (clone $query)->count(),
                'draft' => (clone $query)->whereIn('status', ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'])->count(),
            ];
        });
    }

    private function countSuratMasuk(int $gudangId): array
    {
        $cacheKey = $this->buildSuratJalanCacheKey('count_masuk', $gudangId, []);
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($gudangId) {
            // Exclude SELESAI, DRAFT, dan status persetujuan dari counts
            $query = SuratJalan::where('gudang_tujuan_id', $gudangId)
                ->whereNotIn('status', ['DRAFT', 'SELESAI', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN']);
           return [
                'total' => (clone $query)->count(),
                'menunggu' => (clone $query)->whereIn('status', ['DIKIRIM', 'DIKEMBALIKAN'])->count(),
            ];
        });
    }

    private function resolveAccessibleGudangIds(?User $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->role === 'admin') {
            return [];
        }

        if ($user->role === 'manager') {
            return $user->managedGudangs()->pluck('gudangs.id')->all();
        }

        return $user->gudang_id ? [$user->gudang_id] : [];
    }

    private function resolveManagerSignerId(?int $gudangId): ?int
    {
        if (!$gudangId) {
            return null;
        }

        $gudang = Gudang::find($gudangId);
        if (!$gudang) {
            return null;
        }

        return $gudang->managers()
            ->where('role', 'manager')
            ->orderBy('name')
            ->value('users.id');
    }

    private function buildSuratJalanCacheKey(string $type, int $gudangId, array $filters): string
    {
        $version = Cache::get($this->getSuratJalanCacheVersionKey($gudangId), 1);
        $hash = $filters ? md5(json_encode($filters)) : 'all';
        return "surat_jalan.{$type}.{$gudangId}.v{$version}.{$hash}";
    }

    private function bumpSuratJalanCacheVersion(array $gudangIds): void
    {
        $uniqueIds = collect($gudangIds)
            ->filter(fn ($id) => !empty($id))
            ->unique()
            ->values();

        foreach ($uniqueIds as $gudangId) {
            $key = $this->getSuratJalanCacheVersionKey((int) $gudangId);
            $updated = Cache::increment($key);
            if ($updated === false) {
                Cache::forever($key, 2);
            }
        }
    }

    private function getSuratJalanCacheVersionKey(int $gudangId): string
    {
        return "surat_jalan.version.{$gudangId}";
    }

    private function buildSuratJalanDetailCacheKey(int $suratJalanId): string
    {
        $version = Cache::get($this->getSuratJalanDetailVersionKey($suratJalanId), 1);
        return "surat_jalan.detail.{$suratJalanId}.v{$version}";
    }

    private function bumpSuratJalanDetailCacheVersion(int $suratJalanId): void
    {
        $key = $this->getSuratJalanDetailVersionKey($suratJalanId);
        $updated = Cache::increment($key);
        if ($updated === false) {
            Cache::forever($key, 2);
        }
    }

    private function getSuratJalanDetailVersionKey(int $suratJalanId): string
    {
        return "surat_jalan.detail.version.{$suratJalanId}";
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

    private function buildStockWarnings(int $gudangId, array $items, bool $excludeBorrowed = false): array
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
        $borrowedTotals = $excludeBorrowed
            ? $this->getBorrowedItemTotals($gudangId, $requested->keys())
            : collect();

        $warnings = [];
        foreach ($requested as $itemId => $qty) {
            $available = (int) ($stocks[$itemId] ?? 0);
            if ($excludeBorrowed) {
                $available -= (int) ($borrowedTotals[$itemId] ?? 0);
                $available = max(0, $available);
            }
            if ($qty > $available) {
                $name = $itemNames[$itemId] ?? 'Item';
                $label = $excludeBorrowed ? 'stok sendiri' : 'stok';
                $warnings[] = "{$name} (diminta {$qty}, {$label} {$available})";
            }
        }

        return $warnings;
    }

    private function buildStockErrorMessage(int $gudangId, array $items, bool $excludeBorrowed = false): string
    {
        $requested = collect($items)
            ->filter(fn ($row) => !empty($row['item_id']) && !empty($row['jumlah']))
            ->groupBy('item_id')
            ->map(fn ($rows) => $rows->sum(fn ($row) => (int) $row['jumlah']));

        if ($requested->isEmpty()) {
            return 'Stok barang tidak mencukupi.';
        }

        $stocks = ItemStock::where('gudang_id', $gudangId)
            ->whereIn('item_id', $requested->keys())
            ->pluck('jumlah', 'item_id');

        $itemNames = Item::whereIn('id', $requested->keys())->pluck('nama', 'id');
        $borrowedTotals = $this->getBorrowedItemTotals($gudangId, $requested->keys());

        $details = [];
        foreach ($requested as $itemId => $qty) {
            $stock = (int) ($stocks[$itemId] ?? 0);
            $borrowed = (int) ($borrowedTotals[$itemId] ?? 0);
            $available = max(0, $stock - $borrowed);
            if ($qty > $available) {
                $name = $itemNames[$itemId] ?? 'Item';
                $details[] = "{$name} (diminta {$qty}, tersedia {$available})";
            }
        }

        if (empty($details)) {
            return 'Stok barang tidak mencukupi.';
        }

        return 'Stok barang tidak mencukupi: ' . implode(', ', $details) . '.';
    }

    private function buildItemTotals(array $items)
    {
        return collect($items)
            ->filter(fn ($row) => !empty($row['item_id']) && !empty($row['jumlah']))
            ->groupBy('item_id')
            ->map(fn ($rows) => $rows->sum(fn ($row) => (int) $row['jumlah']));
    }

    private function assertStockAvailable(int $gudangId, $itemTotals, bool $excludeBorrowed = false): void
    {
        $itemNames = Item::whereIn('id', $itemTotals->keys())
            ->pluck('nama', 'id');
        $borrowedTotals = $excludeBorrowed
            ? $this->getBorrowedItemTotals($gudangId, $itemTotals->keys())
            : collect();

        foreach ($itemTotals as $itemId => $qty) {
            $stock = ItemStock::where('gudang_id', $gudangId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            $available = $stock?->jumlah ?? 0;
            if ($excludeBorrowed) {
                $available -= (int) ($borrowedTotals[$itemId] ?? 0);
                $available = max(0, $available);
            }
            if ($available < $qty) {
                $name = $itemNames[$itemId] ?? 'Item';
                $detail = $excludeBorrowed
                    ? "Stok sendiri tidak cukup untuk {$name} (dibutuhkan {$qty}, tersedia {$available}). Barang pinjaman dari gudang lain tidak dapat dipinjamkan."
                    : "Stok tidak cukup untuk {$name} (dibutuhkan {$qty}, tersedia {$available}).";
                throw new \RuntimeException($detail);
            }
        }
    }

    private function getBorrowedItemTotals(int $gudangId, $itemIds = null)
    {
        $query = PeminjamanItem::query()
            ->select('peminjaman_items.item_id', DB::raw('SUM(peminjaman_items.jumlah_dipinjam) as total'))
            ->join('peminjamans', 'peminjaman_items.peminjaman_id', '=', 'peminjamans.id')
            ->where('peminjamans.gudang_peminjam_id', $gudangId)
            ->whereNotIn('peminjamans.status', ['SELESAI', 'DITOLAK'])
            ->where(function ($query) {
                $query->whereNotNull('peminjamans.waktu_diterima')
                    ->orWhereNotNull('peminjamans.waktu_ttd_penerima');
            });

        if ($itemIds !== null) {
            $query->whereIn('peminjaman_items.item_id', collect($itemIds)->all());
        }

        return $query
            ->groupBy('peminjaman_items.item_id')
            ->pluck('total', 'peminjaman_items.item_id');
    }

    private function applyStockOut(int $gudangId, $itemTotals, SuratJalan $suratJalan, Carbon $eventTime, string $keterangan): void
    {
        $movementUserId = $suratJalan->created_by ?: Auth::id();
        foreach ($itemTotals as $itemId => $qty) {
            $stock = ItemStock::where('gudang_id', $gudangId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw new \RuntimeException("Stok item {$itemId} tidak ditemukan.");
            }

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
                'created_by' => $movementUserId,
                'keterangan' => $keterangan,
                'created_at' => $eventTime,
                'updated_at' => $eventTime,
            ]);
        }
    }

    private function applyStockIn(int $gudangId, $itemTotals, SuratJalan $suratJalan, Carbon $eventTime, string $keterangan): void
    {
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
                'keterangan' => $keterangan,
                'created_at' => $eventTime,
                'updated_at' => $eventTime,
            ]);
        }
    }

    private function storeAdminCompletedSuratJalan(
        array $validated,
        int $gudangId,
        Carbon $tanggalKirim,
        ?Carbon $tanggalKembali,
        ?int $picTujuanId,
        int $gudangTujuanId,
        bool $isCustomGudang,
        array $customGudangData,
        ?array $picCustomData,
        ?int $ttdPembuatId,
        ?int $ttdPenerimaId
    ): int {
        $itemTotals = $this->buildItemTotals($validated['items']);
        $kirimAt = $tanggalKirim->copy()->setTime(8, 0);
        $diterimaAt = $tanggalKirim->copy()->setTime(10, 0);
        $kembaliAt = $tanggalKembali ? $tanggalKembali->copy()->setTime(15, 0) : null;
        $selesaiAt = $kembaliAt ? $kembaliAt->copy()->addHour() : $diterimaAt->copy()->addHour();

        $gudangTujuanNama = $isCustomGudang
            ? ($customGudangData['nama'] ?? 'Gudang Lainnya')
            : (Gudang::find($gudangTujuanId)?->nama ?? 'Gudang Tujuan');

        $gudangAsalNama = Gudang::find($gudangId)?->nama ?? 'Gudang Asal';

        return DB::transaction(function () use ($validated, $gudangId, $tanggalKirim, $tanggalKembali, $picTujuanId, $gudangTujuanId, $isCustomGudang, $customGudangData, $picCustomData, $itemTotals, $kirimAt, $diterimaAt, $kembaliAt, $selesaiAt, $gudangTujuanNama, $gudangAsalNama, $ttdPembuatId, $ttdPenerimaId) {
            $this->assertStockAvailable(
                $gudangId,
                $itemTotals,
                in_array(($validated['mode'] ?? null), ['peminjaman', 'transfer'], true)
            );

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
                    'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                    'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                    'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
                    'tipe' => 'TRANSFER',
                    'status' => 'SELESAI',
                    'tanggal' => $tanggalKirim->toDateString(),
                    'created_by' => Auth::id(),
                    'ttd_pembuat_id' => $ttdPembuatId,
                    'waktu_ttd_pembuat' => $kirimAt,
                    'ttd_penerima_id' => null,
                    'waktu_ttd_penerima' => $isCustomGudang ? null : $selesaiAt,
                    'catatan' => $validated['catatan'] ?? null,
                    'nama_driver' => $validated['nama_driver'] ?? null,
                    'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                    'nomor_plat' => $validated['nomor_plat'] ?? null,
                    'pdf_path' => null,
                    'created_at' => $kirimAt,
                    'updated_at' => $selesaiAt,
                ]);

                $this->createSuratJalanItems($suratJalan->id, $validated['items']);

                $this->applyStockOut(
                    $gudangId,
                    $itemTotals,
                    $suratJalan,
                    $kirimAt,
                    "Pengiriman via {$suratJalan->nomor} ke {$gudangTujuanNama}"
                );

                if (!$isCustomGudang) {
                    $this->applyStockIn(
                        $gudangTujuanId,
                        $itemTotals,
                        $suratJalan,
                        $selesaiAt,
                        "Penerimaan via {$suratJalan->nomor} dari {$gudangAsalNama}"
                    );
                }

                return $suratJalan->id;
            }

              $peminjamanStatus = $isCustomGudang ? 'MENUNGGU_DIKEMBALIKAN' : 'DITERIMA';
              $nomorSuratJalan = $this->generateSuratJalanNomor($tanggalKirim);
              $peminjaman = Peminjaman::create([
                  'kode' => $nomorSuratJalan,
                  'gudang_peminjam_id' => $gudangTujuanId,
                  'gudang_peminjam_is_custom' => $isCustomGudang,
                  'gudang_peminjam_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                  'gudang_peminjam_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                  'gudang_peminjam_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                  'gudang_pemilik_id' => $gudangId,
                  'status' => $peminjamanStatus,
                  'waktu_pengajuan' => $kirimAt,
                  'waktu_kirim' => $kirimAt,
                  'waktu_diterima' => $isCustomGudang ? null : $diterimaAt,
                  'waktu_pengembalian' => null,
                  'waktu_selesai' => null,
                  'durasi_hari' => $tanggalKembali ? $tanggalKirim->diffInDays($tanggalKembali) : null,
                  'durasi_jam' => $tanggalKembali ? $tanggalKirim->diffInHours($tanggalKembali) : null,
                  'batas_waktu_kembali' => $tanggalKembali,
                  'catatan_pengiriman' => $validated['catatan'] ?? null,
                  'created_by' => Auth::id(),
                  'created_at' => $kirimAt,
                  'updated_at' => $isCustomGudang ? $kirimAt : $diterimaAt,
              ]);

              $suratJalanStatus = $isCustomGudang ? 'MENUNGGU_DIKEMBALIKAN' : 'DITERIMA';
              $suratJalanKirim = SuratJalan::create([
                  'nomor' => $nomorSuratJalan,
                  'gudang_asal_id' => $gudangId,
                  'gudang_tujuan_id' => $gudangTujuanId,
                  'gudang_tujuan_is_custom' => $isCustomGudang,
                  'gudang_tujuan_custom_nama' => $isCustomGudang ? $customGudangData['nama'] : null,
                  'gudang_tujuan_custom_alamat' => $isCustomGudang ? $customGudangData['alamat'] : null,
                  'gudang_tujuan_custom_telepon' => $isCustomGudang ? $customGudangData['telepon'] : null,
                  'pic_tujuan_id' => $picTujuanId,
                  'pic_tujuan_custom_nama' => $picCustomData['nama'] ?? null,
                  'pic_tujuan_custom_jabatan' => $picCustomData['jabatan'] ?? null,
                  'pic_tujuan_custom_no_hp' => $picCustomData['no_hp'] ?? null,
                  'tipe' => 'PEMINJAMAN',
                  'status' => $suratJalanStatus,
                  'tanggal' => $tanggalKirim->toDateString(),
                  'created_by' => Auth::id(),
                  'ttd_pembuat_id' => $ttdPembuatId,
                  'waktu_ttd_pembuat' => $kirimAt,
                  'ttd_penerima_id' => null,
                  'waktu_ttd_penerima' => $isCustomGudang ? null : $diterimaAt,
                  'catatan' => $validated['catatan'] ?? null,
                  'nama_driver' => $validated['nama_driver'] ?? null,
                  'jenis_kendaraan' => $validated['jenis_kendaraan'] ?? null,
                  'nomor_plat' => $validated['nomor_plat'] ?? null,
                  'pdf_path' => null,
                  'created_at' => $kirimAt,
                  'updated_at' => $isCustomGudang ? $kirimAt : $diterimaAt,
              ]);

            $peminjaman->update([
                'surat_jalan_kirim_id' => $suratJalanKirim->id,
            ]);

            $this->createSuratJalanItems($suratJalanKirim->id, $validated['items']);
            $this->createPeminjamanItems($peminjaman->id, $validated['items']);

            $this->applyStockOut(
                $gudangId,
                $itemTotals,
                $suratJalanKirim,
                $kirimAt,
                "Pengiriman via {$suratJalanKirim->nomor} ke {$gudangTujuanNama}"
            );

            if (!$isCustomGudang) {
                $this->applyStockIn(
                    $gudangTujuanId,
                    $itemTotals,
                    $suratJalanKirim,
                    $diterimaAt,
                    "Penerimaan via {$suratJalanKirim->nomor} dari {$gudangAsalNama}"
                );
            }

              return $suratJalanKirim->id;
          });
      }

    private function seedAdminQuickStatusHistories(
        int $suratJalanId,
        string $mode,
        bool $isCustomGudang,
        Carbon $tanggalKirim,
        ?Carbon $tanggalKembali
    ): void {
        $kirimAt = $tanggalKirim->copy()->setTime(8, 0);
        $periksaAt = $tanggalKirim->copy()->setTime(9, 0);
        $diterimaAt = $tanggalKirim->copy()->setTime(10, 0);
        $selesaiAt = $tanggalKembali
            ? $tanggalKembali->copy()->setTime(16, 0)
            : $diterimaAt->copy()->addHour();

        $entries = [];
        if ($mode === 'transfer') {
            $entries[] = ['status' => 'DIKIRIM', 'occurred_at' => $kirimAt];
            if (!$isCustomGudang) {
                $entries[] = ['status' => 'DIPERIKSA', 'occurred_at' => $periksaAt];
            }
            $entries[] = ['status' => 'SELESAI', 'occurred_at' => $selesaiAt];
        } else {
            $entries[] = ['status' => 'DIKIRIM', 'occurred_at' => $kirimAt];
            if ($isCustomGudang) {
                $entries[] = ['status' => 'MENUNGGU_DIKEMBALIKAN', 'occurred_at' => $diterimaAt];
            } else {
                $entries[] = ['status' => 'DIPERIKSA', 'occurred_at' => $periksaAt];
                $entries[] = ['status' => 'DITERIMA', 'occurred_at' => $diterimaAt];
            }
        }

        $this->replaceStatusHistories($suratJalanId, $entries);
    }

    private function seedAdminReturnStatusHistories(int $suratJalanId, Carbon $tanggalKirim): void
    {
        $kembaliAt = $tanggalKirim->copy()->setTime(15, 0);
        $periksaAt = $kembaliAt->copy()->addMinutes(30);
        $selesaiAt = $kembaliAt->copy()->addHour();

        $this->replaceStatusHistories($suratJalanId, [
            ['status' => 'DIKEMBALIKAN', 'occurred_at' => $kembaliAt],
            ['status' => 'DIPERIKSA', 'occurred_at' => $periksaAt],
            ['status' => 'SELESAI', 'occurred_at' => $selesaiAt],
        ]);
    }

    private function replaceStatusHistories(int $suratJalanId, array $entries): void
    {
        if (!Schema::hasTable('surat_jalan_status_histories')) {
            return;
        }

        SuratJalanStatusHistory::where('surat_jalan_id', $suratJalanId)->delete();

        if (empty($entries)) {
            return;
        }

        $actorId = Auth::id();
        $rows = [];
        foreach ($entries as $entry) {
            if (empty($entry['status']) || empty($entry['occurred_at'])) {
                continue;
            }
            $occurredAt = $entry['occurred_at'];
            $rows[] = [
                'surat_jalan_id' => $suratJalanId,
                'status' => $entry['status'],
                'occurred_at' => $occurredAt,
                'actor_id' => $entry['actor_id'] ?? $actorId,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ];
        }

        if (!empty($rows)) {
            SuratJalanStatusHistory::insert($rows);
        }
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
            $tahun = $tanggal->format('Y');
            $nomor = $prefix . '/' . self::COMPANY_CODE . '/' . $tahun;
        } while (SuratJalan::where('nomor', $nomor)->exists());

        return $nomor;
    }

    private function generatePeminjamanKode(Carbon $tanggal): string
    {
        do {
            $prefix = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $tahun = $tanggal->format('Y');
            $kode = $prefix . '/' . self::COMPANY_CODE . '/' . $tahun;
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
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments', 'statusHistories.actor'])
            ->findOrFail($id);

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
        }

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan', 'peminjaman'));
        $pdf->setPaper('A4', 'portrait');

        $safeNomor = str_replace(['/', '\\'], '-', $suratJalan->nomor);
        return $pdf->download('surat-jalan-' . $safeNomor . '.pdf');
    }

    /**
     * Preview PDF for existing Surat Jalan (inline display)
     */
    public function previewPdf(string $id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments', 'statusHistories.actor'])
            ->findOrFail($id);

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
        }

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('suratJalan', 'peminjaman'));
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
        $suratJalan->setRelation('ttdPembuat', $request->input('ttd_pembuat_id') ? User::find((int) $request->input('ttd_pembuat_id')) : null);
        $suratJalan->setRelation('ttdPenerima', null);

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
     * Copy attachments from source surat jalan to target surat jalan
     */
    private function copyAttachmentsFromSuratJalan(int $targetSuratJalanId, SuratJalan $sourceSuratJalan): void
    {
        foreach ($sourceSuratJalan->attachments as $attachment) {
            // Check if source file exists
            if (!Storage::disk('public')->exists($attachment->file_path)) {
                continue;
            }

            // Generate new file path
            $extension = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
            $newFileName = 'surat-jalan-attachments/' . uniqid() . '_' . time() . '.' . $extension;

            // Copy file to new location
            Storage::disk('public')->copy($attachment->file_path, $newFileName);

            // Create new attachment record
            SuratJalanAttachment::create([
                'surat_jalan_id' => $targetSuratJalanId,
                'file_path' => $newFileName,
                'file_name' => $attachment->file_name,
            ]);
        }
    }

    private function deleteAttachmentsByIds(SuratJalan $suratJalan, array $attachmentIds): void
    {
        $ids = collect($attachmentIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $attachments = SuratJalanAttachment::where('surat_jalan_id', $suratJalan->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            $attachment->delete();
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

        $editableStatuses = ['DRAFT', 'DITOLAK_PERSETUJUAN', 'DITOLAK'];
        if (!in_array($suratJalan->status, $editableStatuses, true)) {
            return redirect()->back()->with('error', 'Lampiran hanya bisa dihapus saat status Draft atau Ditolak.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        return redirect()->back()->with('success', 'Lampiran berhasil dihapus.');
    }

    /**
     * Export Surat Jalan to Excel (Operator Gudang - own gudang only)
     */
    public function exportExcel(Request $request)
    {
        $gudangId = Auth::user()?->gudang_id;
        if (!$gudangId) {
            abort(403, 'User tidak memiliki gudang yang ditugaskan');
        }

        $validated = $request->validate([
            'tipe' => ['nullable', 'string', Rule::in(['ALL', 'TRANSFER', 'PEMINJAMAN', 'PENGEMBALIAN'])],
            'periode' => ['nullable', 'string', Rule::in(['1_minggu', '1_bulan', '3_bulan', '6_bulan', '1_tahun', 'custom'])],
            'tanggal_mulai' => ['nullable', 'date', 'required_if:periode,custom'],
            'tanggal_selesai' => ['nullable', 'date', 'required_if:periode,custom', 'after_or_equal:tanggal_mulai'],
        ]);

        // Calculate date range based on period
        $dates = $this->calculateDateRange(
            $validated['periode'] ?? '1_bulan',
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_selesai'] ?? null
        );

        $fileName = 'surat-jalan-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new SuratJalanExport(
                $gudangId,
                $validated['tipe'] ?? null,
                $dates['start'],
                $dates['end']
            ),
            $fileName
        );
    }

    /**
     * Calculate date range from period option
     */
    private function calculateDateRange(string $periode, ?string $tanggalMulai, ?string $tanggalSelesai): array
    {
        $now = Carbon::now();

        switch ($periode) {
            case '1_minggu':
                return [
                    'start' => $now->copy()->subWeek()->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '1_bulan':
                return [
                    'start' => $now->copy()->subMonth()->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '3_bulan':
                return [
                    'start' => $now->copy()->subMonths(3)->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '6_bulan':
                return [
                    'start' => $now->copy()->subMonths(6)->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '1_tahun':
                return [
                    'start' => $now->copy()->subYear()->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case 'custom':
                return [
                    'start' => $tanggalMulai,
                    'end' => $tanggalSelesai,
                ];
            default:
                return [
                    'start' => $now->copy()->subMonth()->toDateString(),
                    'end' => $now->toDateString(),
                ];
        }
    }
}
