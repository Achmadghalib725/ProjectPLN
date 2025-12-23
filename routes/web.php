<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\PublicSuratJalanController;
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

    // Mengambil data real dari database
    $data = [
        'totalUsers'   => User::count(),
        'totalItems'   => Item::count(),
        'totalGudangs' => Gudang::count(),
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
                ->where('status', '!=', 'SELESAI')
                ->orderByDesc('tanggal')
                ->limit(6)
                ->get()
            : collect(),
        'recentActivities' => $gudangId && Schema::hasTable('stock_movements')
            ? StockMovement::with(['item', 'creator'])
                ->where('gudang_id', $gudangId)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
            : collect(),
        // Stats untuk Security
        'stats' => [
            'diterima_hari_ini' => Schema::hasTable('surat_jalans')
                ? SuratJalan::where('status', 'DITERIMA')
                    ->whereDate('updated_at', today())
                    ->count()
                : 0,
            'menunggu' => Schema::hasTable('surat_jalans')
                ? SuratJalan::where('status', 'DIKIRIM')->count()
                : 0,
        ],
    ];

    // Mengirim variabel $data ke view dashboard
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

// GROUPING BERDASARKAN ROLE
Route::middleware('auth')->group(function () {
    // ... (sisa kode route lainnya tetap sama)
    
    // 1. AREA ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('items', ItemController::class);
        Route::resource('pics', PicController::class);
    });

    // ... (Area Operator & Security tetap sama)
    
    Route::middleware('role:operator_gudang,admin')->prefix('gudang')->name('gudang.')->group(function () {
        // Routes spesifik HARUS sebelum resource route
        Route::get('/stok/barang-dipinjamkan', [StokController::class, 'barangDipinjamkan'])->name('stok.barang-dipinjamkan');
        Route::get('/stok/barang-pinjaman', [StokController::class, 'barangPinjaman'])->name('stok.barang-pinjaman');
        Route::get('/riwayat', [StokController::class, 'riwayat'])->name('riwayat');
        Route::resource('stok', StokController::class);
        Route::get('/surat-jalan/create', [SuratJalanController::class, 'create'])->name('surat-jalan.create');
        Route::get('/surat-jalan/index', [SuratJalanController::class, 'index'])->name('surat-jalan.index');
        Route::post('/surat-jalan', [SuratJalanController::class, 'store'])->name('surat-jalan.store');
        Route::post('/surat-jalan/pengembalian', [SuratJalanController::class, 'storeReturn'])->name('surat-jalan.return');
        Route::get('/surat-jalan/{id}', [SuratJalanController::class, 'show'])->whereNumber('id')->name('surat-jalan.show');
        Route::get('/surat-jalan/{id}/edit', [SuratJalanController::class, 'edit'])->whereNumber('id')->name('surat-jalan.edit');
        Route::patch('/surat-jalan/{id}', [SuratJalanController::class, 'update'])->whereNumber('id')->name('surat-jalan.update');
        Route::post('/surat-jalan/{id}/approve', [SuratJalanController::class, 'approve'])->whereNumber('id')->name('surat-jalan.approve');
        Route::delete('/surat-jalan/{id}', [SuratJalanController::class, 'destroy'])->whereNumber('id')->name('surat-jalan.destroy');
        Route::get('/surat-jalan/{id}/pdf', [SuratJalanController::class, 'generatePdf'])->whereNumber('id')->name('surat-jalan.pdf');
        Route::get('/surat-jalan/{id}/preview', [SuratJalanController::class, 'previewPdf'])->whereNumber('id')->name('surat-jalan.preview');
        Route::post('/surat-jalan/preview', [SuratJalanController::class, 'previewDraft'])->name('surat-jalan.preview-draft');
        Route::post('/surat-jalan/{id}/terima', [SuratJalanController::class, 'terima'])->whereNumber('id')->name('surat-jalan.terima');
        Route::post('/surat-jalan/{id}/confirm-return', [SuratJalanController::class, 'confirmReturnExternal'])->whereNumber('id')->name('surat-jalan.confirm-return');
        Route::delete('/surat-jalan/attachment/{id}', [SuratJalanController::class, 'deleteAttachment'])->whereNumber('id')->name('surat-jalan.delete-attachment');
    });

    Route::middleware('role:security,admin')->prefix('security')->name('security.')->group(function () {
        Route::post('/search', [SecurityController::class, 'search'])->name('search');
        Route::get('/surat-jalan/{id}/qr/{token}', [SecurityController::class, 'showByToken'])->whereNumber('id')->name('qr');
        Route::get('/surat-jalan/{id}', [SecurityController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/surat-jalan/{id}/terima', [SecurityController::class, 'terima'])->whereNumber('id')->name('terima');
        Route::post('/surat-jalan/{id}/tolak', [SecurityController::class, 'tolak'])->whereNumber('id')->name('tolak');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
