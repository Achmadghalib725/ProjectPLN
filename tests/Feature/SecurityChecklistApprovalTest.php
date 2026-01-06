<?php

namespace Tests\Feature;

use App\Models\Gudang;
use App\Models\Item;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityChecklistApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_requires_all_items_checked_before_approve(): void
    {
        [$suratJalan, $securityUser, $itemRows] = $this->createSuratJalanWithItems();

        $response = $this->actingAs($securityUser)->post(route('security.terima', $suratJalan->id), [
            'checked_items' => [$itemRows[0]->id],
        ]);

        $response->assertSessionHas('error', 'Semua item harus ditandai sesuai sebelum konfirmasi pemeriksaan.');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $suratJalan->id,
            'status' => 'DIKIRIM',
        ]);
        $this->assertDatabaseHas('surat_jalan_items', [
            'id' => $itemRows[0]->id,
            'checked_by_security' => null,
        ]);
    }

    public function test_security_can_approve_when_all_items_checked(): void
    {
        [$suratJalan, $securityUser, $itemRows] = $this->createSuratJalanWithItems();
        $checkedIds = $itemRows->pluck('id')->all();

        $response = $this->actingAs($securityUser)->post(route('security.terima', $suratJalan->id), [
            'checked_items' => $checkedIds,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $suratJalan->id,
            'status' => 'DIPERIKSA',
        ]);

        foreach ($checkedIds as $itemId) {
            $this->assertDatabaseHas('surat_jalan_items', [
                'id' => $itemId,
                'checked_by_security' => true,
                'checked_by_user_id' => $securityUser->id,
            ]);
            $this->assertNotNull(SuratJalanItem::find($itemId)->checked_at);
        }
    }

    public function test_security_cannot_approve_for_other_gudang(): void
    {
        [$suratJalan, $securityUser, $itemRows] = $this->createSuratJalanWithItems();
        $otherGudang = $this->makeGudang('OTHER');
        $otherSecurity = $this->makeUser([
            'name' => 'Security Other',
            'username' => 'security_other',
            'email' => 'security_other@example.test',
            'role' => 'security',
            'gudang_id' => $otherGudang->id,
        ]);

        $response = $this->actingAs($otherSecurity)->post(route('security.terima', $suratJalan->id), [
            'checked_items' => $itemRows->pluck('id')->all(),
        ]);

        $response->assertSessionHas('error', 'Anda tidak memiliki akses untuk mengkonfirmasi surat jalan ini. Surat jalan ini ditujukan ke gudang lain.');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $suratJalan->id,
            'status' => 'DIKIRIM',
        ]);
    }

    public function test_security_rejects_items_from_other_surat_jalan(): void
    {
        [$suratJalan, $securityUser, $itemRows] = $this->createSuratJalanWithItems();
        [$otherSuratJalan, , $otherItemRows] = $this->createSuratJalanWithItems();

        $response = $this->actingAs($securityUser)->post(route('security.terima', $suratJalan->id), [
            'checked_items' => [$itemRows[0]->id, $otherItemRows[0]->id],
        ]);

        $response->assertSessionHasErrors('checked_items.1');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $suratJalan->id,
            'status' => 'DIKIRIM',
        ]);
    }

    public function test_security_cannot_approve_when_status_invalid(): void
    {
        [$suratJalan, $securityUser, $itemRows] = $this->createSuratJalanWithItems(['status' => 'DRAFT']);

        $response = $this->actingAs($securityUser)->post(route('security.terima', $suratJalan->id), [
            'checked_items' => $itemRows->pluck('id')->all(),
        ]);

        $response->assertSessionHas('error', 'Surat Jalan ini tidak dalam status yang dapat diperiksa. Status saat ini: DRAFT');
        $this->assertDatabaseHas('surat_jalans', [
            'id' => $suratJalan->id,
            'status' => 'DRAFT',
        ]);
    }

    private function createSuratJalanWithItems(array $suratOverrides = []): array
    {
        $gudangAsal = $this->makeGudang('ASAL');
        $gudangTujuan = $this->makeGudang('TUJUAN');
        $operator = $this->makeUser([
            'name' => 'Operator Asal',
            'username' => 'operator_asal_' . Str::lower(Str::random(6)),
            'email' => 'operator_' . Str::lower(Str::random(6)) . '@example.test',
            'role' => 'operator_gudang',
            'gudang_id' => $gudangAsal->id,
        ]);
        $securityUser = $this->makeUser([
            'name' => 'Security Tujuan',
            'username' => 'security_tujuan_' . Str::lower(Str::random(6)),
            'email' => 'security_' . Str::lower(Str::random(6)) . '@example.test',
            'role' => 'security',
            'gudang_id' => $gudangTujuan->id,
        ]);

        $suratJalan = SuratJalan::create(array_merge([
            'nomor' => 'SJ-' . Str::upper(Str::random(6)),
            'gudang_asal_id' => $gudangAsal->id,
            'gudang_tujuan_id' => $gudangTujuan->id,
            'tipe' => 'TRANSFER',
            'status' => 'DIKIRIM',
            'tanggal' => now()->toDateString(),
            'created_by' => $operator->id,
            'catatan' => null,
        ], $suratOverrides));

        $itemRows = collect();
        for ($i = 1; $i <= 2; $i++) {
            $item = Item::create([
                'kode' => 'ITM-' . Str::upper(Str::random(4)),
                'nama' => 'Item ' . $i,
                'satuan' => 'unit',
                'kategori' => 'kabel',
                'deskripsi' => null,
            ]);

            $itemRows->push(SuratJalanItem::create([
                'surat_jalan_id' => $suratJalan->id,
                'item_id' => $item->id,
                'jumlah' => 1,
                'keterangan' => null,
            ]));
        }

        return [$suratJalan, $securityUser, $itemRows];
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
        return User::create(array_merge([
            'name' => 'User Test',
            'username' => 'user_' . Str::lower(Str::random(6)),
            'email' => 'user_' . Str::lower(Str::random(6)) . '@example.test',
            'password' => 'password',
            'role' => 'security',
            'gudang_id' => null,
        ], $overrides));
    }
}
