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
        // 1. Buat 2 Gudang
        $gudangPusat = Gudang::create([
            'kode' => 'GDG-JKT', 'nama' => 'Gudang Pusat Jakarta', 'alamat' => 'Jl. Pusat'
        ]);
        $gudangCabang = Gudang::create([
            'kode' => 'GDG-BDG', 'nama' => 'Gudang Cabang Bandung', 'alamat' => 'Jl. Asia Afrika'
        ]);

        // 2. Buat User Admin & Operator
        User::create([
            'name' => 'Admin Super',
            'email' => 'admin@pln.co.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Operator Jakarta',
            'email' => 'op.jakarta@pln.co.id',
            'password' => Hash::make('password123'),
            'role' => 'operator_gudang',
            'gudang_id' => $gudangPusat->id,
        ]);

        User::create([
            'name' => 'Danru Security',
            'email' => 'security@pln.co.id',
            'password' => Hash::make('password123'),
            'role' => 'security',
            'gudang_id' => $gudangPusat->id, // Security bertugas di Gudang Pusat
            'jabatan' => 'Komandan Regu'
        ]);

        // 3. Buat Item Dummy
        $item1 = Item::create([
            'kode' => 'KBL-001', 'nama' => 'Kabel NYA 1.5mm', 'satuan' => 'roll', 'kategori' => 'Sparepart'
        ]);
        $item2 = Item::create([
            'kode' => 'TRA-001', 'nama' => 'Trafo 100kVA', 'satuan' => 'unit', 'kategori' => 'Tools'
        ]);

        // 4. Isi Stok Awal di Gudang Pusat
        ItemStock::create(['item_id' => $item1->id, 'gudang_id' => $gudangPusat->id, 'jumlah' => 100]);
        ItemStock::create(['item_id' => $item2->id, 'gudang_id' => $gudangPusat->id, 'jumlah' => 5]);
    }
}