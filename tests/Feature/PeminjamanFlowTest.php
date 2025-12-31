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

    private function seedBaseData(): array
    {
        $gudangA = Gudang::create([
            'kode' => 'GDG-A',
            'nama' => 'Gudang A',
        ]);
        $gudangB = Gudang::create([
            'kode' => 'GDG-B',
            'nama' => 'Gudang B',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin_test',
            'email' => 'admin_test@example.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'username' => 'manager_test',
            'email' => 'manager_test@example.test',
            'password' => 'password',
            'role' => 'manager',
            'is_active' => true,
        ]);
        $manager->managedGudangs()->sync([$gudangA->id, $gudangB->id]);

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

        $picA = Pic::create([
            'nama' => 'PIC Gudang A',
            'jabatan' => 'Koordinator',
            'gudang_id' => $gudangA->id,
        ]);
        $picB = Pic::create([
            'nama' => 'PIC Gudang B',
            'jabatan' => 'Koordinator',
            'gudang_id' => $gudangB->id,
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

        return compact(
            'gudangA',
            'gudangB',
            'admin',
            'manager',
            'operatorA',
            'operatorB',
            'securityA',
            'securityB',
            'picA',
            'picB',
            'item'
        );
    }

    public function test_operator_to_operator_peminjaman_flow_with_manager_approval(): void
    {
        $data = $this->seedBaseData();
        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(2);

        $this->actingAs($data['operatorA'])->post(route('gudang.surat-jalan.store'), [
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $data['gudangB']->id,
            'pic_tujuan_id' => $data['picB']->id,
            'tanggal_kirim' => $tanggalKirim->toDateString(),
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'catatan' => 'Peminjaman awal',
            'nama_driver' => 'Driver Test',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'B 1234 CD',
            'items' => [
                [
                    'item_id' => $data['item']->id,
                    'jumlah' => 3,
                    'keterangan' => 'Peminjaman item',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('gudang_asal_id', $data['gudangA']->id)
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

        $this->actingAs($data['operatorA'])
            ->post(route('gudang.surat-jalan.request-approval', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $this->assertSame('MENUNGGU_PERSETUJUAN', $suratJalan->status);

        $this->actingAs($data['manager'])
            ->post(route('manager.surat-jalan.approve', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIKIRIM', $suratJalan->status);
        $this->assertSame('DIKIRIM', $peminjaman->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangA']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 7,
        ]);

        $this->actingAs($data['securityB'])
            ->post(route('security.terima', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIPERIKSA', $suratJalan->status);
        $this->assertSame('DIPERIKSA', $peminjaman->status);

        $this->actingAs($data['operatorB'])
            ->post(route('gudang.surat-jalan.terima', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DITERIMA', $suratJalan->status);
        $this->assertSame('DITERIMA', $peminjaman->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangB']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 3,
        ]);

        $this->actingAs($data['operatorB'])->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $data['picA']->id,
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

        $this->actingAs($data['operatorB'])
            ->post(route('gudang.surat-jalan.request-approval', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $this->assertSame('MENUNGGU_PERSETUJUAN', $returnSuratJalan->status);

        $this->actingAs($data['manager'])
            ->post(route('manager.surat-jalan.approve', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIKEMBALIKAN', $returnSuratJalan->status);
        $this->assertSame('DIKEMBALIKAN', $peminjaman->status);

        $this->actingAs($data['securityA'])
            ->post(route('security.terima', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIPERIKSA', $returnSuratJalan->status);
        $this->assertSame('DIPERIKSA', $peminjaman->status);

        $this->actingAs($data['operatorA'])
            ->post(route('gudang.surat-jalan.terima', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();
        $suratJalan->refresh();
        $this->assertSame('SELESAI', $returnSuratJalan->status);
        $this->assertSame('SELESAI', $peminjaman->status);
        $this->assertSame('SELESAI', $suratJalan->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangA']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 10,
        ]);
    }

    public function test_admin_to_operator_peminjaman_flow_with_admin_approval(): void
    {
        $data = $this->seedBaseData();
        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(1);

        $this->actingAs($data['admin'])->post(route('gudang.surat-jalan.store'), [
            'gudang_asal_id' => $data['gudangA']->id,
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $data['gudangB']->id,
            'pic_tujuan_id' => $data['picB']->id,
            'tanggal_kirim' => $tanggalKirim->toDateString(),
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'catatan' => 'Peminjaman admin',
            'nama_driver' => 'Driver Admin',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'BE 1234 ZZ',
            'items' => [
                [
                    'item_id' => $data['item']->id,
                    'jumlah' => 4,
                    'keterangan' => 'Pinjam admin',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('gudang_asal_id', $data['gudangA']->id)
            ->where('tipe', 'PEMINJAMAN')
            ->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture-admin.jpg',
            'file_name' => 'fixture-admin.jpg',
        ]);

        $this->actingAs($data['admin'])
            ->post(route('gudang.surat-jalan.request-approval', $suratJalan->id))
            ->assertSessionHas('success');

        $this->actingAs($data['admin'])
            ->post(route('gudang.surat-jalan.approve', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $peminjaman->refresh();
        $this->assertSame('DIKIRIM', $suratJalan->status);
        $this->assertSame('DIKIRIM', $peminjaman->status);

        $this->actingAs($data['securityB'])
            ->post(route('security.terima', $suratJalan->id))
            ->assertSessionHas('success');

        $this->actingAs($data['operatorB'])
            ->post(route('gudang.surat-jalan.terima', $suratJalan->id))
            ->assertSessionHas('success');

        $peminjaman->refresh();
        $this->assertSame('DITERIMA', $peminjaman->status);

        $this->actingAs($data['operatorB'])->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $data['picA']->id,
            'tanggal_kirim' => $tanggalKirim->copy()->addDays(2)->toDateString(),
            'catatan' => 'Pengembalian admin',
            'nama_driver' => 'Driver Return Admin',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'BE 9090 AA',
        ])->assertSessionHas('success');

        $peminjaman->refresh();
        $returnSuratJalan = SuratJalan::findOrFail($peminjaman->surat_jalan_kembali_id);

        SuratJalanAttachment::create([
            'surat_jalan_id' => $returnSuratJalan->id,
            'file_path' => 'tests/fixture-admin-return.jpg',
            'file_name' => 'fixture-admin-return.jpg',
        ]);

        $this->actingAs($data['operatorB'])
            ->post(route('gudang.surat-jalan.request-approval', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $this->actingAs($data['admin'])
            ->post(route('gudang.surat-jalan.approve', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $this->actingAs($data['securityA'])
            ->post(route('security.terima', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $this->actingAs($data['operatorA'])
            ->post(route('gudang.surat-jalan.terima', $returnSuratJalan->id))
            ->assertSessionHas('success');

        $peminjaman->refresh();
        $suratJalan->refresh();
        $returnSuratJalan->refresh();
        $this->assertSame('SELESAI', $peminjaman->status);
        $this->assertSame('SELESAI', $suratJalan->status);
        $this->assertSame('SELESAI', $returnSuratJalan->status);
    }

    public function test_admin_finish_peminjaman_and_return_flow(): void
    {
        $data = $this->seedBaseData();
        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(1);

        $this->actingAs($data['admin'])->post(route('gudang.surat-jalan.store'), [
            'gudang_asal_id' => $data['gudangA']->id,
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $data['gudangB']->id,
            'pic_tujuan_id' => $data['picB']->id,
            'tanggal_kirim' => $tanggalKirim->toDateString(),
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'catatan' => 'Peminjaman admin finish',
            'nama_driver' => 'Driver Admin',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'BE 7777 ZZ',
            'items' => [
                [
                    'item_id' => $data['item']->id,
                    'jumlah' => 2,
                    'keterangan' => 'Pinjam admin finish',
                ],
            ],
            'admin_finish' => 1,
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('gudang_asal_id', $data['gudangA']->id)
            ->where('tipe', 'PEMINJAMAN')
            ->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        $this->assertSame('DITERIMA', $suratJalan->status);
        $this->assertSame('DITERIMA', $peminjaman->status);

        $this->actingAs($data['admin'])->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $data['picA']->id,
            'tanggal_kirim' => $tanggalKirim->copy()->addDays(2)->toDateString(),
            'catatan' => 'Pengembalian admin finish',
            'nama_driver' => 'Driver Return Admin',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'BE 1818 CC',
            'admin_finish' => 1,
        ])->assertSessionHas('success');

        $peminjaman->refresh();
        $returnSuratJalan = SuratJalan::findOrFail($peminjaman->surat_jalan_kembali_id);

        $this->assertSame('SELESAI', $returnSuratJalan->status);
        $this->assertSame('SELESAI', $peminjaman->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangA']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 10,
        ]);
    }
}
