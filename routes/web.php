<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\PublicSuratJalanController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\ManagerSuratJalanController;
use App\Http\Controllers\AdminSuratJalanController;
use App\Http\Controllers\GudangItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ItemMetaController;
use Illuminate\Support\Facades\Route;
// Tambahkan Import Model yang dibutuhkan
use App\Models\User;
use App\Models\Item;
use App\Models\Gudang;
use App\Models\ItemStock;
use App\Models\SuratJalan;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

Route::redirect('/', '/login');

Route::get('/surat-jalan/ttd/{id}/{token}/{role}', [PublicSuratJalanController::class, 'signature'])
    ->whereNumber('id')
    ->name('surat-jalan.signature');

// === UPDATE ROUTE DASHBOARD ===
Route::get('/dashboard', function () {
    $user = Auth::user();
    $gudangId = $user->gudang_id;
    $managerGudangIds = $user->role === 'manager'
        ? $user->managedGudangs()->pluck('gudangs.id')->all()
        : [];
    $managerGudangs = $user->role === 'manager'
        ? $user->managedGudangs()->orderBy('nama')->get()
        : collect();

    // Mengambil data real dari database
    $data = [
        'totalUsers'   => User::count(),
        'totalItems'   => Item::count(),
        'totalGudangs' => Gudang::count(),
        // Gudangs list untuk form rekap admin
        'gudangs'      => Gudang::where('kode', '!=', 'GDG-EXT')->orderBy('nama')->get(),
        'totalStockItems' => $gudangId && Schema::hasTable('item_stocks')
            ? ItemStock::where('gudang_id', $gudangId)->count()
            : 0,
        'totalStockUnits' => $gudangId && Schema::hasTable('item_stocks')
            ? ItemStock::where('gudang_id', $gudangId)->sum('jumlah')
            : 0,
        'lowStockCount' => $gudangId && Schema::hasTable('item_stocks')
            ? ItemStock::where('gudang_id', $gudangId)
                ->whereColumn('jumlah', '<', 'stok_minimum')
                ->count()
            : 0,
        'totalSuratJalan' => $gudangId && Schema::hasTable('surat_jalans')
            ? SuratJalan::where(function ($query) use ($gudangId) {
                $query->where('gudang_asal_id', $gudangId)
                    ->orWhere('gudang_tujuan_id', $gudangId);
            })->count()
            : 0,
        'totalPeminjamanAktif' => $gudangId && Schema::hasTable('peminjamans')
            ? Peminjaman::where('gudang_pemilik_id', $gudangId)
                ->where('status', '!=', 'SELESAI')
                ->count()
            : 0,
        'totalBarangDipinjam' => $gudangId && Schema::hasTable('peminjamans') && Schema::hasTable('peminjaman_items')
            ? PeminjamanItem::whereIn('peminjaman_id', function ($query) use ($gudangId) {
                $query->select('id')
                    ->from('peminjamans')
                    ->where('gudang_pemilik_id', $gudangId)
                    ->where('status', '!=', 'SELESAI');
            })->sum('jumlah_dipinjam')
            : 0,
        'activeSuratJalans' => $gudangId && Schema::hasTable('surat_jalans')
            ? SuratJalan::with(['gudangTujuan', 'gudangAsal'])
                ->where(function ($query) use ($gudangId) {
                    $query->where('gudang_asal_id', $gudangId)
                        ->orWhere('gudang_tujuan_id', $gudangId);
                })
                ->whereNotIn('status', ['DRAFT', 'SELESAI'])
                ->orderByDesc('tanggal')
                ->limit(5)
                ->get()
            : collect(),
        'recentActivities' => $gudangId && Schema::hasTable('stock_movements')
            ? StockMovement::with(['item', 'creator'])
                ->where('gudang_id', $gudangId)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
            : collect(),
        // Stats untuk Security (filter by gudang_tujuan_id untuk security role)
        'stats' => [
            'diterima_hari_ini' => $gudangId && Schema::hasTable('surat_jalans')
                ? SuratJalan::where('gudang_tujuan_id', $gudangId)
                    ->where('status', 'DIPERIKSA')
                    ->whereDate('updated_at', today())
                    ->count()
                : 0,
            'menunggu' => $gudangId && Schema::hasTable('surat_jalans')
                ? SuratJalan::where('gudang_tujuan_id', $gudangId)
                    ->whereIn('status', ['DIKIRIM', 'DIKEMBALIKAN'])
                    ->count()
                : 0,
        ],
        'managerStats' => [
            'total' => 0,
            'menunggu_persetujuan' => 0,
            'ditolak_persetujuan' => 0,
            'dikirim' => 0,
            'selesai' => 0,
        ],
        'managerRecent' => collect(),
        'managerGudangs' => $managerGudangs,
        'divisiStats' => [
            'total' => 0,
            'menunggu' => 0,
            'diterima' => 0,
        ],
        'divisiRecent' => collect(),
    ];

    if ($user->role === 'manager' && Schema::hasTable('surat_jalans') && !empty($managerGudangIds)) {
        $managerQuery = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'pembuat'])
            ->whereIn('gudang_asal_id', $managerGudangIds);

        $data['managerStats'] = [
            'total' => (clone $managerQuery)->count(),
            'menunggu_persetujuan' => (clone $managerQuery)->where('status', 'MENUNGGU_PERSETUJUAN')->count(),
            'ditolak_persetujuan' => (clone $managerQuery)->where('status', 'DITOLAK_PERSETUJUAN')->count(),
            'dikirim' => (clone $managerQuery)->whereIn('status', ['DIKIRIM', 'MENUNGGU_DIKEMBALIKAN'])->count(),
            'selesai' => (clone $managerQuery)->where('status', 'SELESAI')->count(),
        ];
        $data['managerRecent'] = $managerQuery->orderByDesc('tanggal')->limit(6)->get();
    }

    if ($user->role === 'penerima' && $gudangId && Schema::hasTable('surat_jalans') && Schema::hasTable('pics')) {
        $divisi = trim((string) ($user->jabatan ?? ''));
        if ($divisi !== '') {
            $divisiLower = strtolower($divisi);
            $divisiQuery = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan'])
                ->where('gudang_tujuan_id', $gudangId)
                ->whereNotIn('status', ['DRAFT', 'SELESAI', 'MENUNGGU_PERSETUJUAN', 'DITOLAK_PERSETUJUAN'])
                ->whereNotNull('pic_tujuan_id')
                ->whereHas('picTujuan', function ($query) use ($divisiLower) {
                    $query->whereRaw('LOWER(jabatan) = ?', [$divisiLower]);
                });

            $data['divisiStats'] = [
                'total' => (clone $divisiQuery)->count(),
                'menunggu' => (clone $divisiQuery)->where('status', 'DIKIRIM')->count(),
                'diterima' => (clone $divisiQuery)->where('status', 'DITERIMA')->count(),
            ];
            $data['divisiRecent'] = $divisiQuery->orderByDesc('tanggal')->limit(6)->get();
        }
    }

    // Mengirim variabel $data ke view dashboard
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

// GROUPING BERDASARKAN ROLE
Route::middleware('auth')->group(function () {

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'clearAll'])->name('clear-all');
    });

    // 1. AREA ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('items', ItemController::class);
        Route::resource('pics', PicController::class);

        // Kelola Kategori & Satuan
        Route::get('/item-categories', [ItemMetaController::class, 'categories'])->name('item-categories.index');
        Route::post('/item-categories', [ItemMetaController::class, 'storeCategory'])->name('item-categories.store');
        Route::delete('/item-categories/{category}', [ItemMetaController::class, 'destroyCategory'])->name('item-categories.destroy');
        Route::get('/item-units', [ItemMetaController::class, 'units'])->name('item-units.index');
        Route::post('/item-units', [ItemMetaController::class, 'storeUnit'])->name('item-units.store');
        Route::delete('/item-units/{unit}', [ItemMetaController::class, 'destroyUnit'])->name('item-units.destroy');

        // Rekap Surat Jalan
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/export-excel', [RekapController::class, 'exportExcel'])->name('rekap.export-excel');
    });

    // ... (Area Operator & Security tetap sama)
    
    Route::middleware('role:operator_gudang')->prefix('gudang')->name('gudang.')->group(function () {
        Route::get('/riwayat', [StokController::class, 'riwayat'])->name('riwayat');
        Route::resource('stok', StokController::class);
        Route::post('/items', [GudangItemController::class, 'store'])->name('items.store');
        Route::get('/surat-jalan/create', [SuratJalanController::class, 'create'])->name('surat-jalan.create');
        Route::post('/surat-jalan', [SuratJalanController::class, 'store'])->name('surat-jalan.store');
        Route::post('/surat-jalan/pengembalian', [SuratJalanController::class, 'storeReturn'])->name('surat-jalan.return');
        Route::get('/surat-jalan/{id}/edit', [SuratJalanController::class, 'edit'])->whereNumber('id')->name('surat-jalan.edit');
        Route::patch('/surat-jalan/{id}', [SuratJalanController::class, 'update'])->whereNumber('id')->name('surat-jalan.update');
        Route::post('/surat-jalan/{id}/request-approval', [SuratJalanController::class, 'requestApproval'])->whereNumber('id')->name('surat-jalan.request-approval');
        Route::post('/surat-jalan/{id}/approve', [SuratJalanController::class, 'approve'])->whereNumber('id')->name('surat-jalan.approve');
        Route::post('/surat-jalan/{id}/reject-approval', [SuratJalanController::class, 'rejectApproval'])->whereNumber('id')->name('surat-jalan.reject-approval');
        Route::delete('/surat-jalan/{id}', [SuratJalanController::class, 'destroy'])->whereNumber('id')->name('surat-jalan.destroy');
        Route::post('/surat-jalan/preview', [SuratJalanController::class, 'previewDraft'])->name('surat-jalan.preview-draft');
        Route::post('/surat-jalan/{id}/confirm-return', [SuratJalanController::class, 'confirmReturnExternal'])->whereNumber('id')->name('surat-jalan.confirm-return');
        Route::post('/surat-jalan/{id}/finalize-rejected', [SuratJalanController::class, 'finalizeRejected'])->whereNumber('id')->name('surat-jalan.finalize-rejected');
        Route::delete('/surat-jalan/attachment/{id}', [SuratJalanController::class, 'deleteAttachment'])->whereNumber('id')->name('surat-jalan.delete-attachment');
        Route::get('/surat-jalan/export-excel', [SuratJalanController::class, 'exportExcel'])->name('surat-jalan.export-excel');
    });

    Route::middleware('role:operator_gudang,penerima')->prefix('gudang')->name('gudang.')->group(function () {
        Route::get('/surat-jalan/index', [SuratJalanController::class, 'index'])->name('surat-jalan.index');
        Route::get('/surat-jalan/{id}', [SuratJalanController::class, 'show'])->whereNumber('id')->name('surat-jalan.show');
        Route::get('/surat-jalan/{id}/preview', [SuratJalanController::class, 'previewPdf'])->whereNumber('id')->name('surat-jalan.preview');
        Route::get('/surat-jalan/{id}/pdf', [SuratJalanController::class, 'generatePdf'])->whereNumber('id')->name('surat-jalan.pdf');
        Route::post('/surat-jalan/{id}/terima', [SuratJalanController::class, 'terima'])->whereNumber('id')->name('surat-jalan.terima');
    });

    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/surat-jalan', [ManagerSuratJalanController::class, 'index'])->name('surat-jalan.index');
        Route::get('/surat-jalan/{id}', [ManagerSuratJalanController::class, 'show'])->whereNumber('id')->name('surat-jalan.show');
        Route::post('/surat-jalan/{id}/approve', [SuratJalanController::class, 'approve'])->whereNumber('id')->name('surat-jalan.approve');
        Route::post('/surat-jalan/{id}/reject', [SuratJalanController::class, 'rejectApproval'])->whereNumber('id')->name('surat-jalan.reject');
        Route::get('/surat-jalan/{id}/preview', [SuratJalanController::class, 'previewPdf'])->whereNumber('id')->name('surat-jalan.preview');
        Route::get('/surat-jalan/{id}/pdf', [SuratJalanController::class, 'generatePdf'])->whereNumber('id')->name('surat-jalan.pdf');
    });

    // Route khusus Admin Surat Jalan
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/surat-jalan/create', [AdminSuratJalanController::class, 'create'])->name('surat-jalan.create');
        Route::get('/surat-jalan/index', [AdminSuratJalanController::class, 'index'])->name('surat-jalan.index');
        Route::post('/surat-jalan', [AdminSuratJalanController::class, 'store'])->name('surat-jalan.store');
        Route::post('/surat-jalan/pengembalian', [AdminSuratJalanController::class, 'storeReturn'])->name('surat-jalan.return');
        Route::get('/surat-jalan/{id}', [AdminSuratJalanController::class, 'show'])->whereNumber('id')->name('surat-jalan.show');
        Route::get('/surat-jalan/{id}/edit', [AdminSuratJalanController::class, 'edit'])->whereNumber('id')->name('surat-jalan.edit');
        Route::patch('/surat-jalan/{id}', [AdminSuratJalanController::class, 'update'])->whereNumber('id')->name('surat-jalan.update');
        Route::post('/surat-jalan/{id}/request-approval', [AdminSuratJalanController::class, 'requestApproval'])->whereNumber('id')->name('surat-jalan.request-approval');
        Route::post('/surat-jalan/{id}/approve', [AdminSuratJalanController::class, 'approve'])->whereNumber('id')->name('surat-jalan.approve');
        Route::post('/surat-jalan/{id}/reject-approval', [AdminSuratJalanController::class, 'rejectApproval'])->whereNumber('id')->name('surat-jalan.reject-approval');
        Route::delete('/surat-jalan/{id}', [AdminSuratJalanController::class, 'destroy'])->whereNumber('id')->name('surat-jalan.destroy');
        Route::get('/surat-jalan/{id}/pdf', [AdminSuratJalanController::class, 'generatePdf'])->whereNumber('id')->name('surat-jalan.pdf');
        Route::get('/surat-jalan/{id}/preview', [AdminSuratJalanController::class, 'previewPdf'])->whereNumber('id')->name('surat-jalan.preview');
        Route::post('/surat-jalan/preview', [AdminSuratJalanController::class, 'previewDraft'])->name('surat-jalan.preview-draft');
        Route::post('/surat-jalan/{id}/terima', [AdminSuratJalanController::class, 'terima'])->whereNumber('id')->name('surat-jalan.terima');
        Route::post('/surat-jalan/{id}/confirm-return', [AdminSuratJalanController::class, 'confirmReturnExternal'])->whereNumber('id')->name('surat-jalan.confirm-return');
        Route::post('/surat-jalan/{id}/finalize-rejected', [AdminSuratJalanController::class, 'finalizeRejected'])->whereNumber('id')->name('surat-jalan.finalize-rejected');
        Route::delete('/surat-jalan/attachment/{id}', [AdminSuratJalanController::class, 'deleteAttachment'])->whereNumber('id')->name('surat-jalan.delete-attachment');
        Route::get('/surat-jalan/export-excel', [AdminSuratJalanController::class, 'exportExcel'])->name('surat-jalan.export-excel');
    });

    Route::middleware('role:security,admin')->prefix('security')->name('security.')->group(function () {
        Route::post('/search', [SecurityController::class, 'search'])->name('search');
        Route::get('/surat-jalan/{id}/qr/{token}', [SecurityController::class, 'showByToken'])->whereNumber('id')->name('qr');
        Route::get('/surat-jalan/{id}/preview', [SuratJalanController::class, 'previewPdf'])->whereNumber('id')->name('surat-jalan.preview');
        Route::get('/surat-jalan/{id}/pdf', [SuratJalanController::class, 'generatePdf'])->whereNumber('id')->name('surat-jalan.pdf');
        Route::get('/surat-jalan/{id}', [SecurityController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/surat-jalan/{id}/terima', [SecurityController::class, 'terima'])->whereNumber('id')->name('terima');
        Route::post('/surat-jalan/{id}/tolak', [SecurityController::class, 'tolak'])->whereNumber('id')->name('tolak');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
