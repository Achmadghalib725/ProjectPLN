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
use Illuminate\Http\UploadedFile;
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
        $managerA = $this->createUser('Manager A', 'manager_a', 'manager_a@example.test', 'manager', null);
        $managerB = $this->createUser('Manager B', 'manager_b', 'manager_b@example.test', 'manager', null);
        $securityA = $this->createUser('Security A', 'security_a', 'security_a@example.test', 'security', $gudangA->id);
        $securityB = $this->createUser('Security B', 'security_b', 'security_b@example.test', 'security', $gudangB->id);

        $this->attachManager($gudangA, $managerA);
        $this->attachManager($gudangB, $managerB);

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

        Carbon::setTestNow(Carbon::parse('2025-01-01 08:30:00'));
        $requestResponse = $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.request-approval', $suratJalan->id));
        $this->assertSuccess($requestResponse, 'request-approval');

        Carbon::setTestNow(Carbon::parse('2025-01-01 09:00:00'));
        $approveResponse = $this->actingAs($managerA)
            ->post(route('manager.surat-jalan.approve', $suratJalan->id));
        $this->assertSuccess($approveResponse, 'approve');

        Carbon::setTestNow(Carbon::parse('2025-01-01 09:15:00'));
        $securityPengirimResponse = $this->actingAs($securityA)
            ->post(route('security.terima', $suratJalan->id), $this->checkedItemsPayload($suratJalan));
        $this->assertSuccess($securityPengirimResponse, 'security-pengirim');

        Carbon::setTestNow(Carbon::parse('2025-01-01 09:30:00'));
        $securityResponse = $this->actingAs($securityB)
            ->post(route('security.terima', $suratJalan->id), $this->checkedItemsPayload($suratJalan));
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

        Carbon::setTestNow(Carbon::parse('2025-01-03 10:00:00'));
        $returnRequestResponse = $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.request-approval', $returnSuratJalan->id));
        $this->assertSuccess($returnRequestResponse, 'return-request-approval');

        Carbon::setTestNow(Carbon::parse('2025-01-03 12:30:00'));
        $returnApproveResponse = $this->actingAs($managerB)
            ->post(route('manager.surat-jalan.approve', $returnSuratJalan->id));
        $this->assertSuccess($returnApproveResponse, 'return-approve');

        Carbon::setTestNow(Carbon::parse('2025-01-03 13:00:00'));
        $returnSecurityPengirim = $this->actingAs($securityB)
            ->post(route('security.terima', $returnSuratJalan->id), $this->checkedItemsPayload($returnSuratJalan));
        $this->assertSuccess($returnSecurityPengirim, 'return-security-pengirim');

        Carbon::setTestNow(Carbon::parse('2025-01-03 14:00:00'));
        $returnSecurityPenerima = $this->actingAs($securityA)
            ->post(route('security.terima', $returnSuratJalan->id), $this->checkedItemsPayload($returnSuratJalan));
        $this->assertSuccess($returnSecurityPenerima, 'return-security-penerima');

        Carbon::setTestNow(Carbon::parse('2025-01-03 15:00:00'));
        $returnTerimaResponse = $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.terima', $returnSuratJalan->id));
        $this->assertSuccess($returnTerimaResponse, 'return-terima');

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

        $admin = $this->createUser('Admin', 'admin_user', 'admin@example.test', 'admin', $gudangA->id);
        $managerA = $this->createUser('Manager A2', 'manager_a2', 'manager_a2@example.test', 'manager', null);
        $managerB = $this->createUser('Manager B2', 'manager_b2', 'manager_b2@example.test', 'manager', null);
        $this->attachManager($gudangA, $managerA);
        $this->attachManager($gudangB, $managerB);
        $picB = $this->createPic('PIC Gudang B2', $gudangB->id);
        $picA = $this->createPic('PIC Gudang A2', $gudangA->id);

        $item = $this->createItemWithStock($gudangA, 8);

        $storeResponse = $this->actingAs($admin)->post(route('admin.surat-jalan.store'), [
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
            'attachments' => [
                UploadedFile::fake()->image('lampiran-admin.jpg'),
            ],
        ]);
        $this->assertSuccess($storeResponse, 'admin-store');

        $suratJalan = SuratJalan::where('tipe', 'PEMINJAMAN')->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        Carbon::setTestNow(Carbon::parse('2025-02-04 09:00:00'));
        $admin->update(['gudang_id' => $gudangB->id]);
        $admin->refresh();
        $returnResponse = $this->actingAs($admin)->post(route('admin.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $picA->id,
            'tanggal_kirim' => '2025-02-04',
            'catatan' => 'Pengembalian admin',
            'nama_driver' => 'Driver Return',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'B 2222 BB',
            'admin_finish' => 1,
        ]);
        $this->assertSuccess($returnResponse, 'admin-return');

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
        $managerA = $this->createUser('Manager A3', 'manager_a3', 'manager_a3@example.test', 'manager', null);
        $managerB = $this->createUser('Manager B3', 'manager_b3', 'manager_b3@example.test', 'manager', null);
        $securityA = $this->createUser('Security A3', 'security_a3', 'security_a3@example.test', 'security', $gudangA->id);
        $securityB = $this->createUser('Security B3', 'security_b3', 'security_b3@example.test', 'security', $gudangB->id);
        $admin = $this->createUser('Admin 3', 'admin_user3', 'admin3@example.test', 'admin', $gudangB->id);

        $this->attachManager($gudangA, $managerA);
        $this->attachManager($gudangB, $managerB);

        $picB = $this->createPic('PIC Gudang B3', $gudangB->id);
        $picA = $this->createPic('PIC Gudang A3', $gudangA->id);

        $item = $this->createItemWithStock($gudangA, 6);

        $storeResponse = $this->actingAs($operatorA)->post(route('gudang.surat-jalan.store'), [
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
        ]);
        $this->assertSuccess($storeResponse, 'store');

        $suratJalan = SuratJalan::where('tipe', 'PEMINJAMAN')->firstOrFail();
        $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->firstOrFail();

        SuratJalanAttachment::create([
            'surat_jalan_id' => $suratJalan->id,
            'file_path' => 'tests/fixture-opadmin.jpg',
            'file_name' => 'fixture-opadmin.jpg',
        ]);

        Carbon::setTestNow(Carbon::parse('2025-03-01 08:30:00'));
        $requestResponse = $this->actingAs($operatorA)
            ->post(route('gudang.surat-jalan.request-approval', $suratJalan->id));
        $this->assertSuccess($requestResponse, 'request-approval');

        Carbon::setTestNow(Carbon::parse('2025-03-01 09:00:00'));
        $approveResponse = $this->actingAs($managerA)
            ->post(route('manager.surat-jalan.approve', $suratJalan->id));
        $this->assertSuccess($approveResponse, 'approve');

        Carbon::setTestNow(Carbon::parse('2025-03-01 09:15:00'));
        $securityPengirimResponse = $this->actingAs($securityA)
            ->post(route('security.terima', $suratJalan->id), $this->checkedItemsPayload($suratJalan));
        $this->assertSuccess($securityPengirimResponse, 'security-pengirim');

        Carbon::setTestNow(Carbon::parse('2025-03-01 09:30:00'));
        $securityResponse = $this->actingAs($securityB)
            ->post(route('security.terima', $suratJalan->id), $this->checkedItemsPayload($suratJalan));
        $this->assertSuccess($securityResponse, 'security-terima');

        Carbon::setTestNow(Carbon::parse('2025-03-01 11:00:00'));
        $terimaResponse = $this->actingAs($operatorB)
            ->post(route('gudang.surat-jalan.terima', $suratJalan->id));
        $this->assertSuccess($terimaResponse, 'operator-terima');

        Carbon::setTestNow(Carbon::parse('2025-03-02 08:00:00'));
        $returnResponse = $this->actingAs($admin)->post(route('admin.surat-jalan.return'), [
            'peminjaman_id' => $peminjaman->id,
            'pic_tujuan_id' => $picA->id,
            'tanggal_kirim' => '2025-03-02',
            'catatan' => 'Pengembalian admin',
            'nama_driver' => 'Driver Admin',
            'jenis_kendaraan' => 'Pickup',
            'nomor_plat' => 'B 4444 DD',
            'admin_finish' => 1,
        ]);
        $this->assertSuccess($returnResponse, 'admin-return');

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
        try {
            $response->assertSessionHas('success');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $errors = session('errors');
            $messages = $errors ? $errors->all() : [];
            $flashError = session('error');
            if ($flashError) {
                $messages[] = $flashError;
            }
            $status = null;
            $content = '';
            if (property_exists($response, 'baseResponse') && $response->baseResponse) {
                $status = $response->baseResponse->getStatusCode();
                $content = (string) $response->baseResponse->getContent();
            } elseif (method_exists($response, 'status')) {
                $status = $response->status();
            }
            $snippet = $content !== '' ? substr($content, 0, 300) : '';
            throw new \RuntimeException(
                $label . ' failed: ' . json_encode($messages) . ' status=' . $status . ' body=' . json_encode($snippet),
                0,
                $e
            );
        }
    }

    private function attachManager(Gudang $gudang, User $manager): void
    {
        $gudang->managers()->syncWithoutDetaching([$manager->id]);
    }

    private function checkedItemsPayload(SuratJalan $suratJalan): array
    {
        $suratJalan->loadMissing('items');
        return ['checked_items' => $suratJalan->items->pluck('id')->all()];
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
