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
            'email' => 'admin@pln.com',
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
            'email' => 'tarahan@pln.com',
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
            'email' => 'telukbetung@pln.com',
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
            'email' => 'security@pln.com',
            'password' => Hash::make('password'),
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

        Pic::create([
            'nama' => 'Heri Pranata',
            'jabatan' => 'PIC Proyek Area',
            'no_hp' => '0815-7788-9900',
            'gudang_id' => null,
        ]);

        // ========================================
        // 9. BUAT DUMMY SURAT JALAN + ITEM
        // ========================================
        $tanggalHariIni = Carbon::now()->startOfDay();

        $sj1 = SuratJalan::create([
            'nomor' => 'SJ-' . $tanggalHariIni->format('Ymd') . '-001',
            'gudang_asal_id' => $gudangTarahan->id,
            'gudang_tujuan_id' => $gudangTelukBetung->id,
            'pic_tujuan_id' => $picTelukBetung->id,
            'tipe' => 'PEMINJAMAN',
            'status' => 'DRAFT',
            'tanggal' => $tanggalHariIni->toDateString(),
            'created_by' => $operatorTarahan->id,
            'ttd_pembuat_id' => null,
            'waktu_ttd_pembuat' => null,
            'catatan' => 'Dummy surat jalan untuk testing list (draft).',
            'pdf_path' => null,
        ]);

        SuratJalanItem::insert([
            [
                'surat_jalan_id' => $sj1->id,
                'item_id' => $item1->id,
                'jumlah' => 10,
                'keterangan' => 'Kebutuhan instalasi (dummy).',
            ],
            [
                'surat_jalan_id' => $sj1->id,
                'item_id' => $item3->id,
                'jumlah' => 2,
                'keterangan' => 'Cadangan proteksi (dummy).',
            ],
        ]);

        $sj2 = SuratJalan::create([
            'nomor' => 'SJ-' . $tanggalHariIni->copy()->subDay()->format('Ymd') . '-002',
            'gudang_asal_id' => $gudangTelukBetung->id,
            'gudang_tujuan_id' => $gudangTarahan->id,
            'pic_tujuan_id' => $picTarahan->id,
            'tipe' => 'PENGEMBALIAN',
            'status' => 'DIKIRIM',
            'tanggal' => $tanggalHariIni->copy()->subDay()->toDateString(),
            'created_by' => $operatorTelukBetung->id,
            'ttd_pembuat_id' => $operatorTelukBetung->id,
            'waktu_ttd_pembuat' => $tanggalHariIni->copy()->subDay()->setTime(9, 15, 0),
            'catatan' => 'Dummy surat jalan untuk testing list (dikirim).',
            'pdf_path' => null,
        ]);

        SuratJalanItem::create([
            'surat_jalan_id' => $sj2->id,
            'item_id' => $item4->id,
            'jumlah' => 1,
            'keterangan' => 'Pengembalian material (dummy).',
        ]);

        // ========================================
        // 10. DUMMY PEMINJAMAN AKTIF (TELUK <-> TARAHAN)
        // ========================================
        $sjPeminjamanTeluk = SuratJalan::create([
            'nomor' => 'SJ-' . $tanggalHariIni->copy()->subDays(2)->format('Ymd') . '-010',
            'gudang_asal_id' => $gudangTarahan->id,
            'gudang_tujuan_id' => $gudangTelukBetung->id,
            'pic_tujuan_id' => $picTelukBetung->id,
            'tipe' => 'PEMINJAMAN',
            'status' => 'DIKIRIM',
            'tanggal' => $tanggalHariIni->copy()->subDays(2)->toDateString(),
            'created_by' => $operatorTarahan->id,
            'catatan' => 'Peminjaman aktif untuk Teluk Betung (dummy).',
            'pdf_path' => null,
        ]);

        $peminjamanTeluk = Peminjaman::create([
            'kode' => 'PMJ-' . $tanggalHariIni->copy()->subDays(2)->format('Ymd') . '-001',
            'gudang_peminjam_id' => $gudangTelukBetung->id,
            'gudang_pemilik_id' => $gudangTarahan->id,
            'status' => 'DITERIMA',
            'surat_jalan_kirim_id' => $sjPeminjamanTeluk->id,
            'waktu_pengajuan' => $tanggalHariIni->copy()->subDays(2)->setTime(8, 0, 0),
            'waktu_kirim' => $tanggalHariIni->copy()->subDays(2)->setTime(9, 0, 0),
            'waktu_diterima' => $tanggalHariIni->copy()->subDays(2)->setTime(15, 30, 0),
            'durasi_hari' => 3,
            'durasi_jam' => 72,
            'catatan_pengiriman' => 'Peminjaman barang untuk kebutuhan darurat (dummy).',
            'created_by' => $operatorTarahan->id,
        ]);

        PeminjamanItem::insert([
            [
                'peminjaman_id' => $peminjamanTeluk->id,
                'item_id' => $item1->id,
                'jumlah_dipinjam' => 20,
                'jumlah_diterima' => 20,
                'jumlah_dikembalikan' => null,
                'kondisi_kembali' => null,
                'catatan' => 'Dummy peminjaman Teluk Betung.',
            ],
            [
                'peminjaman_id' => $peminjamanTeluk->id,
                'item_id' => $item3->id,
                'jumlah_dipinjam' => 3,
                'jumlah_diterima' => 3,
                'jumlah_dikembalikan' => null,
                'kondisi_kembali' => null,
                'catatan' => 'Dummy peminjaman Teluk Betung.',
            ],
        ]);

        $sjPeminjamanTarahan = SuratJalan::create([
            'nomor' => 'SJ-' . $tanggalHariIni->copy()->subDays(4)->format('Ymd') . '-011',
            'gudang_asal_id' => $gudangTelukBetung->id,
            'gudang_tujuan_id' => $gudangTarahan->id,
            'pic_tujuan_id' => $picTarahan->id,
            'tipe' => 'PEMINJAMAN',
            'status' => 'DIKIRIM',
            'tanggal' => $tanggalHariIni->copy()->subDays(4)->toDateString(),
            'created_by' => $operatorTelukBetung->id,
            'catatan' => 'Peminjaman aktif untuk Tarahan (dummy).',
            'pdf_path' => null,
        ]);

        $peminjamanTarahan = Peminjaman::create([
            'kode' => 'PMJ-' . $tanggalHariIni->copy()->subDays(4)->format('Ymd') . '-001',
            'gudang_peminjam_id' => $gudangTarahan->id,
            'gudang_pemilik_id' => $gudangTelukBetung->id,
            'status' => 'DIKIRIM',
            'surat_jalan_kirim_id' => $sjPeminjamanTarahan->id,
            'waktu_pengajuan' => $tanggalHariIni->copy()->subDays(4)->setTime(8, 30, 0),
            'waktu_kirim' => $tanggalHariIni->copy()->subDays(4)->setTime(10, 0, 0),
            'durasi_hari' => 5,
            'durasi_jam' => 120,
            'catatan_pengiriman' => 'Peminjaman barang untuk proyek (dummy).',
            'created_by' => $operatorTelukBetung->id,
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $peminjamanTarahan->id,
            'item_id' => $item4->id,
            'jumlah_dipinjam' => 2,
            'jumlah_diterima' => null,
            'jumlah_dikembalikan' => null,
            'kondisi_kembali' => null,
            'catatan' => 'Dummy peminjaman Tarahan.',
        ]);
    }
}
