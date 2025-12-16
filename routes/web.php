<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route Dashboard Umum (Semua yang login bisa masuk sini)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// GROUPING BERDASARKAN ROLE
Route::middleware('auth')->group(function () {

    // 1. AREA ADMIN: Kelola Master Data (Barang, User, PIC)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Nanti buat controller: AdminUserController, AdminItemController
        Route::get('/users', function() { return "Halaman Kelola User"; })->name('users.index');
        Route::get('/items', function() { return "Halaman Master Barang"; })->name('items.index');
    });

    // 2. AREA OPERATOR: Inventaris & Surat Jalan
    // Kita izinkan admin akses juga (opsional)
    Route::middleware('role:operator_gudang,admin')->prefix('gudang')->name('gudang.')->group(function () {
        
        // CRUD Inventaris
        Route::get('/stok', function() { return "Halaman Stok Barang"; })->name('stok.index');
        
        // Surat Jalan Digital
        Route::get('/surat-jalan/create', function() { return "Form Buat Surat Jalan"; })->name('surat-jalan.create');
        Route::get('/surat-jalan/cetak/{id}', function() { return "Cetak PDF"; })->name('surat-jalan.print');
        
        // Konfirmasi Terima/Kembali (Operator Gudang Tujuan)
        Route::post('/terima-barang/{id}', function() { return "Proses Terima"; })->name('barang.terima');
    });

    // 3. AREA SECURITY: Cek Validasi Keluar Masuk
    Route::middleware('role:security,admin')->prefix('security')->name('security.')->group(function () {
        Route::get('/scan-qr', function() { return "Halaman Scan QR Surat Jalan"; })->name('scan');
        Route::post('/validasi-gate/{id}', function() { return "Konfirmasi Barang Lewat Gate"; })->name('validasi');
    });

    // Profile (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
