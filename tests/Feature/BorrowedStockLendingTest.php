<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\SuratJalan;
use App\Models\SuratJalanAttachment;
use App\Models\SuratJalanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BorrowedStockLendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_borrowed_items_are_not_available_for_new_loans(): void
    {
        $gudangA = Gudang::create([
            'kode' => 'GDG-A',
            'nama' => 'Gudang A',
        ]);
        $gudangB = Gudang::create([
            'kode' => 'GDG-B',
            'nama' => 'Gudang B',
        ]);
        $gudangC = Gudang::create([
            'kode' => 'GDG-C',
            'nama' => 'Gudang C',
        ]);

        $userA = User::create([
            'name' => 'Operator A',
            'username' => 'operator_a',
            'email' => 'operator_a@example.test',
            'password' => 'password',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangA->id,
            'is_active' => true,
        ]);
        $manager = User::create([
            'name' => 'Manager A',
            'username' => 'manager_a',
            'email' => 'manager_a@example.test',
            'password' => 'password',
            'role' => 'manager',
            'is_active' => true,
        ]);
        $manager->managedGudangs()->sync([$gudangA->id]);

        $item = Item::create([
            'kode' => 'ITM-001',
            'nama' => 'Item Test',
            'satuan' => 'pcs',
            'kategori' => 'Tools',
        ]);

        ItemStock::create([
            'item_id' => $item->id,
            'gudang_id' => $gudangA->id,
            'jumlah' => 10,
            'stok_minimum' => 0,
        ]);

        $peminjaman = Peminjaman::create([
            'kode' => 'PMJ-' . Str::upper(Str::random(6)),
            'gudang_peminjam_id' => $gudangA->id,
            'gudang_pemilik_id' => $gudangB->id,
            'status' => 'DITERIMA',
            'waktu_pengajuan' => now(),
            'waktu_diterima' => now(),
            'created_by' => $userA->id,
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $peminjaman->id,
            'item_id' => $item->id,
            'jumlah_dipinjam' => 8,
        ]);

        $suratJalan = SuratJalan::create([
            'nomor' => 'SJ-' . Str::upper(Str::random(6)),
            'gudang_asal_id' => $gudangA->id,
            'gudang_tujuan_id' => $gudangC->id,
            'tipe' => 'PEMINJAMAN',
            'status' => 'DRAFT',
            'tanggal' => now()->toDateString(),
            'created_by' => $userA->id,
            'gudang_tujuan_is_custom' => false,
        ]);

        SuratJalanItem::create([
            'surat_jalan_id' => $suratJalan->id,
            'item_id' => $item->id,
            'jumlah' => 5,
        ]);

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture.jpg',
            'file_name' => 'fixture.jpg',
        ]);

        $this->actingAs($userA)
            ->post(route('gudang.surat-jalan.request-approval', $suratJalan->id))
            ->assertSessionHas('success');

        $response = $this->actingAs($manager)
            ->post(route('manager.surat-jalan.approve', $suratJalan->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $suratJalan->id,
            'status' => 'MENUNGGU_PERSETUJUAN',
        ]);
        $this->assertDatabaseHas('item_stocks', [
            'item_id' => $item->id,
            'gudang_id' => $gudangA->id,
            'jumlah' => 10,
        ]);
    }
}
