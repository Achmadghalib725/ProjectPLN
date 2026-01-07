<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Item;
use App\Models\Pic;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PenerimaRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_penerima_sees_only_matching_division_on_index(): void
    {
        $gudangAsal = $this->makeGudang('ASAL');
        $gudangTujuan = $this->makeGudang('TUJUAN');
        $creator = $this->makeUser([
            'name' => 'Operator',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangAsal->id,
        ]);
        $penerima = $this->makeUser([
            'name' => 'Penerima K3',
            'role' => 'penerima',
            'gudang_id' => $gudangTujuan->id,
            'jabatan' => 'K3',
        ]);

        $picK3 = $this->makePic($gudangTujuan, 'k3');
        $picOther = $this->makePic($gudangTujuan, 'Pemeliharaan');

        $suratMatch = $this->makeSuratJalan($gudangAsal, $gudangTujuan, $picK3, $creator, [
            'status' => 'DIKIRIM',
        ]);
        $suratOther = $this->makeSuratJalan($gudangAsal, $gudangTujuan, $picOther, $creator, [
            'status' => 'DIKIRIM',
        ]);

        $response = $this->actingAs($penerima)->get(route('gudang.surat-jalan.index'));

        $response->assertOk();
        $response->assertViewHas('suratJalans', function ($paginator) use ($suratMatch, $suratOther) {
            $items = $paginator->getCollection();
            return $items->contains('id', $suratMatch->id)
                && !$items->contains('id', $suratOther->id);
        });
    }

    public function test_penerima_cannot_view_surat_jalan_from_other_division(): void
    {
        $gudangAsal = $this->makeGudang('ASAL');
        $gudangTujuan = $this->makeGudang('TUJUAN');
        $creator = $this->makeUser([
            'name' => 'Operator',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangAsal->id,
        ]);
        $penerima = $this->makeUser([
            'name' => 'Penerima K3',
            'role' => 'penerima',
            'gudang_id' => $gudangTujuan->id,
            'jabatan' => 'K3',
        ]);

        $picOther = $this->makePic($gudangTujuan, 'Pemeliharaan');
        $suratOther = $this->makeSuratJalan($gudangAsal, $gudangTujuan, $picOther, $creator, [
            'status' => 'DIKIRIM',
        ]);

        $response = $this->actingAs($penerima)->get(route('gudang.surat-jalan.show', $suratOther->id));

        $response->assertForbidden();
    }

    public function test_penerima_cannot_accept_before_security_check(): void
    {
        $gudangAsal = $this->makeGudang('ASAL');
        $gudangTujuan = $this->makeGudang('TUJUAN');
        $creator = $this->makeUser([
            'name' => 'Operator',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangAsal->id,
        ]);
        $penerima = $this->makeUser([
            'name' => 'Penerima K3',
            'role' => 'penerima',
            'gudang_id' => $gudangTujuan->id,
            'jabatan' => 'K3',
        ]);

        $picK3 = $this->makePic($gudangTujuan, 'K3');
        $surat = $this->makeSuratJalan($gudangAsal, $gudangTujuan, $picK3, $creator, [
            'status' => 'DIKIRIM',
        ]);
        $this->attachItems($surat, 1);

        $response = $this->actingAs($penerima)->post(route('gudang.surat-jalan.terima', $surat->id));

        $response->assertSessionHas('error', 'Surat Jalan ini belum diperiksa oleh security.');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $surat->id,
            'status' => 'DIKIRIM',
        ]);
    }

    public function test_penerima_can_accept_when_checked_and_division_matches(): void
    {
        $gudangAsal = $this->makeGudang('ASAL');
        $gudangTujuan = $this->makeGudang('TUJUAN');
        $creator = $this->makeUser([
            'name' => 'Operator',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangAsal->id,
        ]);
        $penerima = $this->makeUser([
            'name' => 'Penerima K3',
            'role' => 'penerima',
            'gudang_id' => $gudangTujuan->id,
            'jabatan' => 'K3',
        ]);

        $picK3 = $this->makePic($gudangTujuan, 'K3');
        $surat = $this->makeSuratJalan($gudangAsal, $gudangTujuan, $picK3, $creator, [
            'status' => 'DIPERIKSA',
        ]);
        $items = $this->attachItems($surat, 2);

        $response = $this->actingAs($penerima)->post(route('gudang.surat-jalan.terima', $surat->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $surat->id,
            'status' => 'SELESAI',
        ]);

        foreach ($items as $item) {
            $this->assertDatabaseHas('item_stocks', [
                'gudang_id' => $gudangTujuan->id,
                'item_id' => $item->item_id,
                'jumlah' => $item->jumlah,
            ]);
            $this->assertDatabaseHas('stock_movements', [
                'gudang_id' => $gudangTujuan->id,
                'item_id' => $item->item_id,
                'tipe' => 'IN',
                'jumlah' => $item->jumlah,
            ]);
        }
    }

    public function test_penerima_cannot_access_operator_create_route(): void
    {
        $gudang = $this->makeGudang('TUJUAN');
        $penerima = $this->makeUser([
            'name' => 'Penerima',
            'role' => 'penerima',
            'gudang_id' => $gudang->id,
            'jabatan' => 'K3',
        ]);

        $response = $this->actingAs($penerima)->get(route('gudang.surat-jalan.create'));

        $response->assertForbidden();
    }

    private function makeGudang(string $suffix): Gudang
    {
        return Gudang::create([
            'kode' => 'GDG-' . $suffix . '-' . Str::upper(Str::random(4)),
            'nama' => 'Gudang ' . $suffix,
            'alamat' => null,
            'telepon' => null,
        ]);
    }

    private function makeUser(array $overrides): User
    {
        $seed = Str::lower(Str::random(6));

        return User::create(array_merge([
            'name' => 'User ' . $seed,
            'username' => 'user_' . $seed,
            'email' => 'user_' . $seed . '@example.test',
            'password' => 'password',
            'role' => 'operator_gudang',
            'gudang_id' => null,
            'jabatan' => null,
        ], $overrides));
    }

    private function makePic(Gudang $gudang, string $jabatan): Pic
    {
        return Pic::create([
            'nama' => 'PIC ' . $jabatan,
            'jabatan' => $jabatan,
            'no_hp' => '0812' . rand(100000, 999999),
            'gudang_id' => $gudang->id,
        ]);
    }

    private function makeSuratJalan(Gudang $asal, Gudang $tujuan, Pic $pic, User $creator, array $overrides = []): SuratJalan
    {
        return SuratJalan::create(array_merge([
            'nomor' => 'SJ-' . Str::upper(Str::random(6)),
            'gudang_asal_id' => $asal->id,
            'gudang_tujuan_id' => $tujuan->id,
            'pic_tujuan_id' => $pic->id,
            'tipe' => 'TRANSFER',
            'status' => 'DIKIRIM',
            'tanggal' => now()->toDateString(),
            'created_by' => $creator->id,
            'catatan' => null,
        ], $overrides));
    }

    private function attachItems(SuratJalan $suratJalan, int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $item = Item::create([
                'kode' => 'ITM-' . Str::upper(Str::random(4)),
                'nama' => 'Item ' . ($i + 1),
                'satuan' => 'unit',
                'kategori' => 'kabel',
                'deskripsi' => null,
            ]);

            $items[] = SuratJalanItem::create([
                'surat_jalan_id' => $suratJalan->id,
                'item_id' => $item->id,
                'jumlah' => 1,
                'keterangan' => null,
            ]);
        }

        return $items;
    }
}
