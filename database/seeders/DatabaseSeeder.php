<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Gudang;
use App\Models\Pic;

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
        // 3. BUAT MANAGER (TARAHAN + TELUK BETUNG)
        // ========================================
        $managerGudang = User::create([
            'name' => 'Mega Sukmawan',
            'username' => 'manager',
            'email' => 'manager@egudang.local',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'jabatan' => 'MANAGER ULPLTD/G Tanjung Karang',
            'no_hp' => '081299998888',
            'is_active' => true
        ]);
        $managerGudang->managedGudangs()->sync([$gudangTarahan->id, $gudangTelukBetung->id]);

        // ========================================
        // 4. BUAT 2 OPERATOR GUDANG
        // ========================================

        // Operator Gudang Tarahan
        User::create([
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
        User::create([
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
        // 5. BUAT USER SECURITY
        // ========================================
        User::create([
            'name' => 'Agus Priyanto',
            'username' => 'agus',
            'email' => 'agus@egudang.local',
            'password' => Hash::make('password'),
            'role' => 'security',
            'gudang_id' => $gudangTarahan->id,
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
            'gudang_id' => $gudangTelukBetung->id,
            'jabatan' => 'Petugas Security',
            'no_hp' => '085678901234',
            'is_active' => true
        ]);

        // ========================================
        // 6. MASTER PIC (DATA NON-USER)
        // ========================================
        Pic::create([
            'nama' => 'Rama Kurniawan',
            'jabatan' => 'PIC Logistik Tarahan',
            'no_hp' => '0812-3456-7890',
            'gudang_id' => $gudangTarahan->id,
        ]);

        Pic::create([
            'nama' => 'Dewi Lestari',
            'jabatan' => 'PIC Gudang Teluk Betung',
            'no_hp' => '0813-9876-5432',
            'gudang_id' => $gudangTelukBetung->id,
        ]);

        // ========================================
        // 7. PANGGIL ITEM MEKANIK SEEDER
        // ========================================
        $this->call(ItemMekanikSeeder::class);
    }
}
