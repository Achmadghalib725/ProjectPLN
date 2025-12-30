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

class PeminjamanLogicGuardTest extends TestCase
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

        return compact('gudangA', 'gudangB', 'admin', 'manager', 'operatorA', 'picA', 'picB', 'item');
    }

    public function test_store_rejects_pic_outside_target_gudang(): void
    {
        $data = $this->seedBaseData();
        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(1);

        $this->actingAs($data['operatorA'])->post(route('gudang.surat-jalan.store'), [
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $data['gudangB']->id,
            'pic_tujuan_id' => $data['picA']->id,
            'tanggal_kirim' => $tanggalKirim->toDateString(),
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'catatan' => 'Uji PIC salah gudang',
            'nama_driver' => 'Driver Test',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'B 1234 CD',
            'items' => [
                [
                    'item_id' => $data['item']->id,
                    'jumlah' => 1,
                    'keterangan' => 'Uji',
                ],
            ],
        ])->assertSessionHasErrors('pic_tujuan_id');
    }

    public function test_store_rejects_item_not_in_gudang_stock(): void
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

        $picB = Pic::create([
            'nama' => 'PIC Gudang B',
            'jabatan' => 'Koordinator',
            'gudang_id' => $gudangB->id,
        ]);

        $item = Item::create([
            'kode' => 'ITM-002',
            'nama' => 'Item Tidak Ada Stok',
            'satuan' => 'pcs',
            'kategori' => 'Tools',
        ]);

        ItemStock::create([
            'item_id' => $item->id,
            'gudang_id' => $gudangB->id,
            'jumlah' => 5,
            'stok_minimum' => 0,
        ]);

        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(1);

        $this->actingAs($operatorA)->post(route('gudang.surat-jalan.store'), [
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $gudangB->id,
            'pic_tujuan_id' => $picB->id,
            'tanggal_kirim' => $tanggalKirim->toDateString(),
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'catatan' => 'Uji item bukan stok gudang',
            'nama_driver' => 'Driver Test',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'B 2222 CD',
            'items' => [
                [
                    'item_id' => $item->id,
                    'jumlah' => 1,
                    'keterangan' => 'Uji',
                ],
            ],
        ])->assertSessionHasErrors('items.0.item_id');
    }

    public function test_manager_approval_sets_signer_and_reduces_stock(): void
    {
        $data = $this->seedBaseData();
        $tanggalKirim = now()->startOfDay();
        $tanggalKembali = $tanggalKirim->copy()->addDays(1);

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
                    'jumlah' => 4,
                    'keterangan' => 'Peminjaman item',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('gudang_asal_id', $data['gudangA']->id)
            ->where('tipe', 'PEMINJAMAN')
            ->firstOrFail();

        $this->assertNull($suratJalan->ttd_pembuat_id);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangA']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 10,
        ]);

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture.jpg',
            'file_name' => 'fixture.jpg',
        ]);

        $this->actingAs($data['operatorA'])
            ->post(route('gudang.surat-jalan.request-approval', $suratJalan->id))
            ->assertSessionHas('success');

        $this->actingAs($data['manager'])
            ->post(route('manager.surat-jalan.approve', $suratJalan->id))
            ->assertSessionHas('success');

        $suratJalan->refresh();
        $this->assertSame($data['manager']->id, $suratJalan->ttd_pembuat_id);
        $this->assertSame('DIKIRIM', $suratJalan->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangA']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 6,
        ]);
    }

    public function test_admin_approval_sets_admin_as_signer(): void
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
                    'jumlah' => 2,
                    'keterangan' => 'Pinjam admin',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('gudang_asal_id', $data['gudangA']->id)
            ->where('tipe', 'PEMINJAMAN')
            ->firstOrFail();

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
        $this->assertSame($data['admin']->id, $suratJalan->ttd_pembuat_id);
        $this->assertSame('DIKIRIM', $suratJalan->status);
        $this->assertDatabaseHas('item_stocks', [
            'gudang_id' => $data['gudangA']->id,
            'item_id' => $data['item']->id,
            'jumlah' => 8,
        ]);
    }
}
