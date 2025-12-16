<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
// Tambahkan Import Model yang dibutuhkan
use App\Models\User;
use App\Models\Item;
use App\Models\Gudang;

Route::get('/', function () {
    return view('welcome');
});

// === UPDATE ROUTE DASHBOARD ===
Route::get('/dashboard', function () {
    // Mengambil data real dari database
    $data = [
        'totalUsers'   => User::count(),
        'totalItems'   => Item::count(),
        'totalGudangs' => Gudang::count(),
    ];

    // Mengirim variabel $data ke view dashboard
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

// GROUPING BERDASARKAN ROLE
Route::middleware('auth')->group(function () {
    // ... (sisa kode route lainnya tetap sama)
    
    // 1. AREA ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', function() { return "Halaman Kelola User"; })->name('users.index');
        Route::get('/items', function() { return "Halaman Master Barang"; })->name('items.index');
    });

    // ... (Area Operator & Security tetap sama)
    
    Route::middleware('role:operator_gudang,admin')->prefix('gudang')->name('gudang.')->group(function () {
        Route::get('/stok', function() { return "Halaman Stok Barang"; })->name('stok.index');
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