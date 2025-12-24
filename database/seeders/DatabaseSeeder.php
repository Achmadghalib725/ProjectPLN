<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Gudang;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Pic;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // 1. BUAT 2 GUDANG PLN LAMPUNG
        // ========================================
        $gudangTarahan = Gudang::create([
            'kode' => 'GDG-TRH',
            'nama' => 'Gudang PLTD/G Tarahan',
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
            'username' => 'admin',
            'email' => 'admin@egudang.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan' => 'Administrator Sistem',
            'no_hp' => '081234567890',
            'is_active' => true
        ]);

        // ========================================
        // 3. BUAT 2 OPERATOR GUDANG
        // ========================================

        // Operator Gudang Tarahan
        $operatorTarahan = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@egudang.local',
            'password' => Hash::make('password'),
            'role' => 'operator_gudang',
            'gudang_id' => $gudangTarahan->id,
            'jabatan' => 'Operator Gudang PLTD/G Tarahan',
            'no_hp' => '082345678901',
            'is_active' => true
        ]);

        // Operator Gudang Teluk Betung
        $operatorTelukBetung = User::create([
            'name' => 'Siti Rahma',
            'username' => 'siti',
            'email' => 'siti@egudang.local',
            'password' => Hash::make('password'),
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
            'username' => 'agus',
            'email' => 'agus@egudang.local',
            'password' => Hash::make('password'),
            'role' => 'security',
            'gudang_id' => $gudangTarahan->id, // Security di Tarahan
            'jabatan' => 'Komandan Regu Security',
            'no_hp' => '084567890123',
            'is_active' => true
        ]);

        User::create([
            'name' => 'Rizal Mahendra',
            'username' => 'rizal',
            'email' => 'rizal@egudang.local',
            'password' => Hash::make('password'),
            'role' => 'security',
            'gudang_id' => $gudangTelukBetung->id, // Security di Teluk Betung
            'jabatan' => 'Petugas Security',
            'no_hp' => '085678901234',
            'is_active' => true
        ]);

        // ========================================
        // 5. BUAT MASTER ITEM (Barang)
        // ========================================
        $item1 = Item::create([
            'kode' => 'KBL-001',
            'nama' => 'Kabel NYA 1.5mm',
            'satuan' => 'roll',
            'kategori' => 'kabel',
            'deskripsi' => 'Kabel instalasi rumah NYA ukuran 1.5mm'
        ]);

        $item2 = Item::create([
            'kode' => 'TRA-001',
            'nama' => 'Trafo Distribusi 100kVA',
            'satuan' => 'unit',
            'kategori' => 'trafo',
            'deskripsi' => 'Trafo distribusi 20kV/380V kapasitas 100kVA'
        ]);

        $item3 = Item::create([
            'kode' => 'MCB-001',
            'nama' => 'MCB 3 Phase 63A',
            'satuan' => 'unit',
            'kategori' => 'proteksi',
            'deskripsi' => 'Miniature Circuit Breaker 3 phase 63 Ampere'
        ]);

        $item4 = Item::create([
            'kode' => 'TNG-001',
            'nama' => 'Tiang Beton 9 Meter',
            'satuan' => 'batang',
            'kategori' => 'konstruksi',
            'deskripsi' => 'Tiang beton pracetak tinggi 9 meter'
        ]);

        $item5 = Item::create([
            'kode' => 'KWH-001',
            'nama' => 'KWH Meter Digital 1 Phase',
            'satuan' => 'unit',
            'kategori' => 'meter',
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

        // ========================================
        // 8. MASTER PIC (DATA NON-USER)
        // ========================================
        $picTarahan = Pic::create([
            'nama' => 'Rama Kurniawan',
            'jabatan' => 'PIC Logistik Tarahan',
            'no_hp' => '0812-3456-7890',
            'gudang_id' => $gudangTarahan->id,
        ]);

        $picTelukBetung = Pic::create([
            'nama' => 'Dewi Lestari',
            'jabatan' => 'PIC Gudang Teluk Betung',
            'no_hp' => '0813-9876-5432',
            'gudang_id' => $gudangTelukBetung->id,
        ]);


    }
}
