<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StokController;
use Illuminate\Support\Facades\Route;
// Tambahkan Import Model yang dibutuhkan
use App\Models\User;
use App\Models\Item;
use App\Models\Gudang;
use App\Models\ItemStock;
use Illuminate\Support\Facades\Auth;

Route::redirect('/', '/login');

// === UPDATE ROUTE DASHBOARD ===
Route::get('/dashboard', function () {
    $user = Auth::user();
    $gudangId = $user->gudang_id;

    // Mengambil data real dari database
    $data = [
        'totalUsers'   => User::count(),
        'totalItems'   => Item::count(),
        'totalGudangs' => Gudang::count(),
        'totalStockItems' => $gudangId ? ItemStock::where('gudang_id', $gudangId)->count() : 0,
        'lowStockCount' => $gudangId ? ItemStock::where('gudang_id', $gudangId)
            ->whereColumn('jumlah', '<', 'stok_minimum')
            ->count() : 0
    ];

    // Mengirim variabel $data ke view dashboard
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

// GROUPING BERDASARKAN ROLE
Route::middleware('auth')->group(function () {
    // ... (sisa kode route lainnya tetap sama)
    
    // 1. AREA ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::get('/items', function() { return "Halaman Master Barang"; })->name('items.index');
        Route::get('/gudangs', function() { return "Halaman Kelola Gudang"; })->name('gudangs.index');
    });

    // ... (Area Operator & Security tetap sama)
    
    Route::middleware('role:operator_gudang,admin')->prefix('gudang')->name('gudang.')->group(function () {
        Route::resource('stok', StokController::class);
        Route::get('/surat-jalan/create', function() { return "Form Buat Surat Jalan"; })->name('surat-jalan.create');
        Route::get('/surat-jalan/cetak/{id}', function() { return "Cetak PDF"; })->name('surat-jalan.print');
        Route::post('/terima-barang/{id}', function() { return "Proses Terima"; })->name('barang.terima');
    });

    Route::middleware('role:security,admin')->prefix('security')->name('security.')->group(function () {
        Route::get('/scan-qr', function() { return "Halaman Scan QR Surat Jalan"; })->name('scan');
        Route::post('/validasi-gate/{id}', function() { return "Konfirmasi Barang Lewat Gate"; })->name('validasi');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';