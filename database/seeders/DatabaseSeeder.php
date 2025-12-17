<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Gudang;
use App\Models\Item;
use App\Models\ItemStock;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // 1. BUAT 2 GUDANG PLN LAMPUNG
        // ========================================
        $gudangTarahan = Gudang::create([
            'kode' => 'GDG-TRH',
            'nama' => 'Gudang PLTU Tarahan',
            'alamat' => 'Jl. Raya Tarahan, Kotaagung, Tanggamus, Lampung',
            'telepon' => '0721-5678901'
        ]);

        $gudangTelukBetung = Gudang::create([
            'kode' => 'GDG-TLK',
            'nama' => 'Gudang PLTD Teluk Betung',
            'alamat' => 'Jl. Yos Sudarso, Teluk Betung, Bandar Lampung',
            'telepon' => '0721-1234567'
        ]);

        // ========================================
        // 2. BUAT USER ADMIN
        // ========================================
        User::create([
            'name' => 'Admin PLN Lampung',
            'email' => 'admin@pln.co.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'jabatan' => 'Administrator Sistem',
            'no_hp' => '081234567890',
            'is_active' => true
        ]);

        // ========================================
        // 3. BUAT 2 OPERATOR GUDANG
        // ========================================

        // Operator Gudang Tarahan
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'operator.tarahan@pln.co.id',
            'password' => Hash::make('tarahan123'),
            'role' => 'operator_gudang',
            'gudang_id' => $gudangTarahan->id,
            'jabatan' => 'Operator Gudang PLTU Tarahan',
            'no_hp' => '082345678901',
            'is_active' => true
        ]);

        // Operator Gudang Teluk Betung
        User::create([
            'name' => 'Siti Rahma',
            'email' => 'operator.telukbetung@pln.co.id',
            'password' => Hash::make('telukbetung123'),
            'role' => 'operator_gudang',
            'gudang_id' => $gudangTelukBetung->id,
            'jabatan' => 'Operator Gudang PLTD Teluk Betung',
            'no_hp' => '083456789012',
            'is_active' => true
        ]);

        // ========================================
        // 4. BUAT USER SECURITY
        // ========================================
        User::create([
            'name' => 'Agus Priyanto',
            'email' => 'security@pln.co.id',
            'password' => Hash::make('security123'),
            'role' => 'security',
            'gudang_id' => $gudangTarahan->id, // Security di Tarahan
            'jabatan' => 'Komandan Regu Security',
            'no_hp' => '084567890123',
            'is_active' => true
        ]);

        // ========================================
        // 5. BUAT MASTER ITEM (Barang)
        // ========================================
        $item1 = Item::create([
            'kode' => 'KBL-001',
            'nama' => 'Kabel NYA 1.5mm',
            'satuan' => 'roll',
            'kategori' => 'Kabel',
            'deskripsi' => 'Kabel instalasi rumah NYA ukuran 1.5mm'
        ]);

        $item2 = Item::create([
            'kode' => 'TRA-001',
            'nama' => 'Trafo Distribusi 100kVA',
            'satuan' => 'unit',
            'kategori' => 'Trafo',
            'deskripsi' => 'Trafo distribusi 20kV/380V kapasitas 100kVA'
        ]);

        $item3 = Item::create([
            'kode' => 'MCB-001',
            'nama' => 'MCB 3 Phase 63A',
            'satuan' => 'unit',
            'kategori' => 'Proteksi',
            'deskripsi' => 'Miniature Circuit Breaker 3 phase 63 Ampere'
        ]);

        $item4 = Item::create([
            'kode' => 'TNG-001',
            'nama' => 'Tiang Beton 9 Meter',
            'satuan' => 'batang',
            'kategori' => 'Konstruksi',
            'deskripsi' => 'Tiang beton pracetak tinggi 9 meter'
        ]);

        $item5 = Item::create([
            'kode' => 'KWH-001',
            'nama' => 'KWH Meter Digital 1 Phase',
            'satuan' => 'unit',
            'kategori' => 'Meter',
            'deskripsi' => 'KWH meter digital 1 phase dengan fitur prepaid'
        ]);

        // ========================================
        // 6. ISI STOK AWAL GUDANG TARAHAN
        // ========================================
        ItemStock::create([
            'item_id' => $item1->id,
            'gudang_id' => $gudangTarahan->id,
            'jumlah' => 150,
            'stok_minimum' => 50
        ]);

        ItemStock::create([
            'item_id' => $item2->id,
            'gudang_id' => $gudangTarahan->id,
            'jumlah' => 8,
            'stok_minimum' => 3
        ]);

        ItemStock::create([
            'item_id' => $item3->id,
            'gudang_id' => $gudangTarahan->id,
            'jumlah' => 25,
            'stok_minimum' => 10
        ]);

        // ========================================
        // 7. ISI STOK AWAL GUDANG TELUK BETUNG
        // ========================================
        ItemStock::create([
            'item_id' => $item1->id,
            'gudang_id' => $gudangTelukBetung->id,
            'jumlah' => 80,
            'stok_minimum' => 30
        ]);

        ItemStock::create([
            'item_id' => $item4->id,
            'gudang_id' => $gudangTelukBetung->id,
            'jumlah' => 45,
            'stok_minimum' => 20
        ]);

        ItemStock::create([
            'item_id' => $item5->id,
            'gudang_id' => $gudangTelukBetung->id,
            'jumlah' => 5,
            'stok_minimum' => 15  // Stok rendah untuk testing
        ]);
    }
}