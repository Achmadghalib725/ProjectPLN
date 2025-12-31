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
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReturnDurationPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_operator_to_operator_return_pdf_shows_duration(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 08:00:00'));

        $gudangA = $this->createGudang('GDG-A', 'Gudang A');
        $gudangB = $this->createGudang('GDG-B', 'Gudang B');

        $operatorA = $this->createUser('Operator A', 'operator_a', 'operator_a@example.test', 'operator_gudang', $gudangA->id);
        $operatorB = $this->createUser('Operator B', 'operator_b', 'operator_b@example.test', 'operator_gudang', $gudangB->id);
        $securityB = $this->createUser('Security B', 'security_b', 'security_b@example.test', 'security', $gudangB->id);

        $picB = $this->createPic('PIC Gudang B', $gudangB->id);
        $picA = $this->createPic('PIC Gudang A', $gudangA->id);

        $item = $this->createItemWithStock($gudangA, 10);

        $storeResponse = $this->actingAs($operatorA)->post(route('gudang.surat-jalan.store'), [
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $gudangB->id,
            'pic_tujuan_id' => $picB->id,
            'tanggal_kirim' => '2025-01-01',
            'tanggal_kembali' => '2025-01-05',
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
        ]);
        $this->assertSuccess($storeResponse, 'store');

        $suratJalan = SuratJalan::where('tipe', 'PEMINJAMAN')->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture.jpg',
            'file_name' => 'fixture.jpg',
        ]);

        Carbon::setTestNow(Carbon::parse('2025-01-01 09:00:00'));
        $approveResponse = $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.approve', $suratJalan->id));
        $this->assertSuccess($approveResponse, 'approve');

        Carbon::setTestNow(Carbon::parse('2025-01-01 09:30:00'));
        $securityResponse = $this->actingAs($securityB)
            ->post(route('security.terima', $suratJalan->id));
        $this->assertSuccess($securityResponse, 'security-terima');

        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));
        $terimaResponse = $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.terima', $suratJalan->id));
        $this->assertSuccess($terimaResponse, 'operator-terima');

        Carbon::setTestNow(Carbon::parse('2025-01-03 09:00:00'));
        $returnResponse = $this->actingAs($operatorB)->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $picA->id,
            'tanggal_kirim' => '2025-01-03',
            'catatan' => 'Pengembalian',
            'nama_driver' => 'Driver Return',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'B 9876 EF',
        ]);
        $this->assertSuccess($returnResponse, 'return');

        $peminjaman->refresh();
        $returnSuratJalan = SuratJalan::findOrFail($peminjaman->surat_jalan_kembali_id);

        SuratJalanAttachment::create([
            'surat_jalan_id' => $returnSuratJalan->id,
            'file_path' => 'tests/fixture-return.jpg',
            'file_name' => 'fixture-return.jpg',
        ]);

        Carbon::setTestNow(Carbon::parse('2025-01-03 12:30:00'));
        $returnApproveResponse = $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.approve', $returnSuratJalan->id));
        $this->assertSuccess($returnApproveResponse, 'return-approve');

        $returnSuratJalan->refresh();
        $peminjaman->refresh();

        $returnSuratJalan->load(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments']);
        $html = view('pdf.surat-jalan', [
            'suratJalan' => $returnSuratJalan,
            'peminjaman' => $peminjaman,
        ])->render();

        $lamaPinjamText = $this->buildLamaPinjamText($peminjaman, $returnSuratJalan);
        $this->assertNotNull($lamaPinjamText);
        $this->assertStringContainsString('dipinjam selama', $html);
        $this->assertStringContainsString($lamaPinjamText, $html);
    }

    public function test_admin_to_admin_return_pdf_shows_duration(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-02-01 07:00:00'));

        $gudangA = $this->createGudang('GDG-A2', 'Gudang A2');
        $gudangB = $this->createGudang('GDG-B2', 'Gudang B2');

        $admin = $this->createUser('Admin', 'admin_user', 'admin@example.test', 'admin', null);
        $picB = $this->createPic('PIC Gudang B2', $gudangB->id);
        $picA = $this->createPic('PIC Gudang A2', $gudangA->id);

        $item = $this->createItemWithStock($gudangA, 8);

        $this->actingAs($admin)->post(route('gudang.surat-jalan.store'), [
            'gudang_asal_id' => $gudangA->id,
            'ttd_pembuat_id' => $admin->id,
            'admin_finish' => 1,
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $gudangB->id,
            'pic_tujuan_id' => $picB->id,
            'tanggal_kirim' => '2025-02-01',
            'tanggal_kembali' => '2025-02-05',
            'catatan' => 'Peminjaman admin',
            'nama_driver' => 'Driver Admin',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'B 1111 AA',
            'items' => [
                [
                    'item_id' => $item->id,
                    'jumlah' => 2,
                    'keterangan' => 'Peminjaman admin',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('tipe', 'PEMINJAMAN')->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        Carbon::setTestNow(Carbon::parse('2025-02-04 09:00:00'));
        $this->actingAs($admin)->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $picA->id,
            'tanggal_kirim' => '2025-02-04',
            'catatan' => 'Pengembalian admin',
            'nama_driver' => 'Driver Return',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'B 2222 BB',
            'admin_finish' => 1,
        ])->assertSessionHas('success');

        $peminjaman->refresh();
        $returnSuratJalan = SuratJalan::findOrFail($peminjaman->surat_jalan_kembali_id);

        $returnSuratJalan->load(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments']);
        $html = view('pdf.surat-jalan', [
            'suratJalan' => $returnSuratJalan,
            'peminjaman' => $peminjaman,
        ])->render();

        $lamaPinjamText = $this->buildLamaPinjamText($peminjaman, $returnSuratJalan);
        $this->assertNotNull($lamaPinjamText);
        $this->assertStringContainsString('dipinjam selama', $html);
        $this->assertStringContainsString($lamaPinjamText, $html);
    }

    public function test_operator_to_admin_return_pdf_shows_duration(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-01 08:00:00'));

        $gudangA = $this->createGudang('GDG-A3', 'Gudang A3');
        $gudangB = $this->createGudang('GDG-B3', 'Gudang B3');

        $operatorA = $this->createUser('Operator A3', 'operator_a3', 'operator_a3@example.test', 'operator_gudang', $gudangA->id);
        $operatorB = $this->createUser('Operator B3', 'operator_b3', 'operator_b3@example.test', 'operator_gudang', $gudangB->id);
        $securityB = $this->createUser('Security B3', 'security_b3', 'security_b3@example.test', 'security', $gudangB->id);
        $admin = $this->createUser('Admin 3', 'admin_user3', 'admin3@example.test', 'admin', null);

        $picB = $this->createPic('PIC Gudang B3', $gudangB->id);
        $picA = $this->createPic('PIC Gudang A3', $gudangA->id);

        $item = $this->createItemWithStock($gudangA, 6);

        $this->actingAs($operatorA)->post(route('gudang.surat-jalan.store'), [
            'mode' => 'peminjaman',
            'gudang_tujuan_mode' => 'existing',
            'gudang_tujuan_id' => $gudangB->id,
            'pic_tujuan_id' => $picB->id,
            'tanggal_kirim' => '2025-03-01',
            'tanggal_kembali' => '2025-03-05',
            'catatan' => 'Peminjaman operator',
            'nama_driver' => 'Driver Operator',
            'jenis_kendaraan' => 'Box',
            'nomor_plat' => 'B 3333 CC',
            'items' => [
                [
                    'item_id' => $item->id,
                    'jumlah' => 2,
                    'keterangan' => 'Peminjaman operator',
                ],
            ],
        ])->assertSessionHas('success');

        $suratJalan = SuratJalan::where('tipe', 'PEMINJAMAN')->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture-opadmin.jpg',
            'file_name' => 'fixture-opadmin.jpg',
        ]);

        Carbon::setTestNow(Carbon::parse('2025-03-01 09:00:00'));
        $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.approve', $suratJalan->id))
            ->assertSessionHas('success');

        Carbon::setTestNow(Carbon::parse('2025-03-01 09:30:00'));
        $this->actingAs($securityB)
            ->post(route('security.terima', $suratJalan->id))
            ->assertSessionHas('success');

        Carbon::setTestNow(Carbon::parse('2025-03-01 11:00:00'));
        $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.terima', $suratJalan->id))
            ->assertSessionHas('success');

        Carbon::setTestNow(Carbon::parse('2025-03-02 08:00:00'));
        $this->actingAs($admin)->post(route('gudang.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $picA->id,
            'tanggal_kirim' => '2025-03-02',
            'catatan' => 'Pengembalian admin',
            'nama_driver' => 'Driver Admin',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'B 4444 DD',
            'admin_finish' => 1,
        ])->assertSessionHas('success');

        $peminjaman->refresh();
        $returnSuratJalan = SuratJalan::findOrFail($peminjaman->surat_jalan_kembali_id);

        $returnSuratJalan->load(['gudangAsal', 'gudangTujuan', 'picTujuan', 'pembuat', 'items.item', 'ttdPembuat', 'ttdPenerima', 'attachments']);
        $html = view('pdf.surat-jalan', [
            'suratJalan' => $returnSuratJalan,
            'peminjaman' => $peminjaman,
        ])->render();

        $lamaPinjamText = $this->buildLamaPinjamText($peminjaman, $returnSuratJalan);
        $this->assertNotNull($lamaPinjamText);
        $this->assertStringContainsString('dipinjam selama', $html);
        $this->assertStringContainsString($lamaPinjamText, $html);
    }

    private function createGudang(string $kode, string $nama): Gudang
    {
        return Gudang::create([
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }

    private function createUser(string $name, string $username, string $email, string $role, ?int $gudangId): User
    {
        return User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'gudang_id' => $gudangId,
            'is_active' => true,
        ]);
    }

    private function createPic(string $nama, int $gudangId): Pic
    {
        return Pic::create([
            'nama' => $nama,
            'jabatan' => 'Koordinator',
            'gudang_id' => $gudangId,
        ]);
    }

    private function createItemWithStock(Gudang $gudang, int $jumlah): Item
    {
        $item = Item::create([
            'kode' => 'ITM-' . strtoupper(substr($gudang->kode, -2)),
            'nama' => 'Item Test ' . $gudang->kode,
            'satuan' => 'pcs',
            'kategori' => 'Tools',
        ]);

        ItemStock::create([
            'item_id' => $item->id,
            'gudang_id' => $gudang->id,
            'jumlah' => $jumlah,
            'stok_minimum' => 0,
        ]);

        return $item;
    }

    private function assertSuccess($response, string $label): void
    {
        if (!$response->getSession()->has('success')) {
            $errors = $response->getSession()->get('errors');
            $messages = $errors ? $errors->all() : [];
            throw new \RuntimeException($label . ' failed: ' . json_encode($messages));
        }

        $response->assertSessionHas('success');
    }

    private function buildLamaPinjamText(Peminjaman $peminjaman, SuratJalan $suratJalan): ?string
    {
        $mulaiPinjam = $peminjaman->waktu_diterima
            ?? $peminjaman->waktu_ttd_penerima
            ?? $peminjaman->waktu_kirim
            ?? $peminjaman->waktu_pengajuan
            ?? $peminjaman->created_at;

        $selesaiPinjam = $peminjaman->waktu_pengembalian
            ?? $peminjaman->waktu_selesai
            ?? $suratJalan->created_at
            ?? $suratJalan->tanggal;

        if (!$mulaiPinjam || !$selesaiPinjam) {
            return null;
        }

        $mulaiPinjam = $mulaiPinjam instanceof \Carbon\CarbonInterface
            ? $mulaiPinjam
            : Carbon::parse($mulaiPinjam);
        $selesaiPinjam = $selesaiPinjam instanceof \Carbon\CarbonInterface
            ? $selesaiPinjam
            : Carbon::parse($selesaiPinjam);

        if ($selesaiPinjam->lessThan($mulaiPinjam)) {
            $selesaiPinjam = $mulaiPinjam->copy();
        }

        $totalMinutes = $mulaiPinjam->diffInMinutes($selesaiPinjam);
        $days = intdiv($totalMinutes, 1440);
        $hours = intdiv($totalMinutes % 1440, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%d hari %d jam %d menit', $days, $hours, $minutes);
    }
}
