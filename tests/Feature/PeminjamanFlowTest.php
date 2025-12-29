<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Pic;
use App\Models\Peminjaman;
use App\Models\SuratJalan;
use App\Models\SuratJalanAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_peminjaman_flow_from_request_to_return_completion(): void
    {
        $gudangA = Gudang::create([
            'kode' => 'GDG-A',
            'nama' => 'Gudang A',
        ]);
        $gudangB = Gudang::create([
            'kode' => 'GDG-B',
            'nama' => 'Gudang B',
        ]);

        $operatorA = User::create([
            'name' => 'Operator A',
            'username' => 'operator_a',
            'email' => 'operator_a@example.test',
            'password' => 'password',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangA->id,
            'is_active' => true,
        ]);
        $operatorB = User::create([
            'name' => 'Operator B',
            'username' => 'operator_b',
            'email' => 'operator_b@example.test',
            'password' => 'password',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangB->id,
            'is_active' => true,
        ]);
        $securityA = User::create([
            'name' => 'Security A',
            'username' => 'security_a',
            'email' => 'security_a@example.test',
            'password' => 'password',
            'role' => 'security',
            'gudang_id' => $gudangA->id,
            'is_active' => true,
        ]);
        $securityB = User::create([
            'name' => 'Security B',
            'username' => 'security_b',
            'email' => 'security_b@example.test',
            'password' => 'password',
            'role' => 'security',
            'gudang_id' => $gudangB->id,
            'is_active' => true,
        ]);

        $picB = Pic::create([
            'nama' => 'PIC Gudang B',
            'jabatan' => 'Koordinator',
            'gudang_id' => $gudangB->id,
        ]);
        $picA = Pic::create([
            'nama' => 'PIC Gudang A',
            'jabatan' => 'Koordinator',
            'gudang_id' => $gudangA->id,
        ]);

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

        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(2);

        $this->actingAs($operatorA)->post(route('gudang.surat-jalan.store'), [
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $gudangB->id,
            'pic_tujuan_id' => $picB->id,
            'tanggal_kirim' => $tanggalKirim->toDateString(),
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'catatan' => 'Peminjaman awal',
            'nama_driver' => 'Driver Test',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'B 1234 CD',
            'items' => [
                [
                    'item_id' => $item->id,
                    'jumlah' => 3,
                    'keterangan' => 'Peminjaman item',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('gudang_asal_id', $gudangA->id)
            ->where('tipe', 'PEMINJAMAN')
            ->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        $this->assertSame('DRAFT', $suratJalan->status);
        $this->assertSame('DIAJUKAN', $peminjaman->status);

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture.jpg',
            'file_name' => 'fixture.jpg',
        ]);

        $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.approve', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIKIRIM', $suratJalan->status);
        $this->assertSame('DIKIRIM', $peminjaman->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $gudangA->id,
            'item_id' => $item->id,
            'jumlah' => 7,
        ]);

        $this->actingAs($securityB)
            ->post(route('security.terima', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIPERIKSA', $suratJalan->status);
        $this->assertSame('DIPERIKSA', $peminjaman->status);

        $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.terima', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DITERIMA', $suratJalan->status);
        $this->assertSame('DITERIMA', $peminjaman->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $gudangB->id,
            'item_id' => $item->id,
            'jumlah' => 3,
        ]);

        $this->actingAs($operatorB)->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $picA->id,
            'tanggal_kirim' => $tanggalKirim->copy()->addDays(3)->toDateString(),
            'catatan' => 'Pengembalian',
            'nama_driver' => 'Driver Return',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'B 9876 EF',
        ])->assertSessionHas('success');

        $peminjaman->refresh();
        $returnSuratJalan = SuratJalan::findOrFail($peminjaman->surat_jalan_kembali_id);

        $this->assertSame('PENGEMBALIAN', $returnSuratJalan->tipe);
        $this->assertSame('DRAFT', $returnSuratJalan->status);

        SuratJalanAttachment::create([
            'surat_jalan_id' => $returnSuratJalan->id,
            'file_path' => 'tests/fixture-return.jpg',
            'file_name' => 'fixture-return.jpg',
        ]);

        $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.approve', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIKEMBALIKAN', $returnSuratJalan->status);
        $this->assertSame('DIKEMBALIKAN', $peminjaman->status);

        $this->actingAs($securityA)
            ->post(route('security.terima', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIPERIKSA', $returnSuratJalan->status);
        $this->assertSame('DIPERIKSA', $peminjaman->status);

        $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.terima', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();
        $suratJalan->refresh();

        $this->assertSame('SELESAI', $returnSuratJalan->status);
        $this->assertSame('SELESAI', $peminjaman->status);
        $this->assertSame('SELESAI', $suratJalan->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $gudangA->id,
            'item_id' => $item->id,
            'jumlah' => 10,
        ]);
    }
}
