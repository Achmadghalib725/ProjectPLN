<?php

namespace App\Observers;

use App\Events\SuratJalanStatusUpdated;
use App\Models\AppNotification;
use App\Models\SuratJalan;
use App\Models\SuratJalanStatusHistory;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SuratJalanObserver
{
    public function created(SuratJalan $suratJalan): void
    {
        $this->recordStatusHistory($suratJalan);

        try {
            event(new SuratJalanStatusUpdated($suratJalan, 'created'));
        } catch (BroadcastException $e) {
            Log::warning('Broadcasting failed for SuratJalan created: ' . $e->getMessage());
        }
    }

    public function updated(SuratJalan $suratJalan): void
    {
        if (!$suratJalan->wasChanged('status')) {
            return;
        }

        $previousStatus = $suratJalan->getOriginal('status');
        $this->recordStatusHistory($suratJalan);

        // Broadcast event
        try {
            event(new SuratJalanStatusUpdated($suratJalan, 'status_updated'));
        } catch (BroadcastException $e) {
            Log::warning('Broadcasting failed for SuratJalan status_updated: ' . $e->getMessage());
        }

        // Create notifications based on status
        $this->createNotifications($suratJalan, $previousStatus);
    }

    protected function createNotifications(SuratJalan $suratJalan, ?string $previousStatus = null): void
    {
        $status = $suratJalan->status;
        $nomor = $suratJalan->nomor;
        $gudangAsal = $suratJalan->gudangAsal?->nama ?? 'Gudang Asal';
        $gudangTujuan = $suratJalan->gudangTujuan?->nama ?? 'Gudang Tujuan';
        $picUserId = $suratJalan->picTujuan?->user_id;
        $targetJabatan = $suratJalan->picTujuan?->jabatan ?? $suratJalan->pic_tujuan_custom_jabatan;
        $targetJabatan = trim((string) $targetJabatan);

        try {
            switch ($status) {
                case 'DIKIRIM':
                    // Notify operator gudang tujuan: ada surat masuk
                    if ($suratJalan->gudang_tujuan_id) {
                        $roles = $suratJalan->tipe === 'PENGEMBALIAN' || $targetJabatan === ''
                            ? ['operator_gudang']
                            : ['operator_gudang', 'penerima'];
                        AppNotification::notifyGudangOperators(
                            $suratJalan->gudang_tujuan_id,
                            AppNotification::TYPE_SURAT_MASUK,
                            'Surat Jalan Masuk',
                            "Surat jalan {$nomor} dari {$gudangAsal} sedang dalam perjalanan ke gudang Anda.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id),
                            $roles,
                            $targetJabatan !== '' ? $targetJabatan : null
                        );
                    }
                    if ($picUserId) {
                        AppNotification::notifyUser(
                            $picUserId,
                            AppNotification::TYPE_SURAT_MASUK,
                            'Surat Jalan Masuk',
                            "Surat jalan {$nomor} dari {$gudangAsal} sedang dalam perjalanan ke gudang Anda.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id)
                        );
                    }
                    break;

                case 'DIPERIKSA':
                case 'DIPERIKSA_PENERIMA':
                    // Notify operator gudang tujuan: surat siap diterima
                    if ($suratJalan->gudang_tujuan_id) {
                        $roles = $targetJabatan === '' ? ['operator_gudang'] : ['operator_gudang', 'penerima'];
                        AppNotification::notifyGudangOperators(
                            $suratJalan->gudang_tujuan_id,
                            AppNotification::TYPE_SURAT_SIAP_TERIMA,
                            'Surat Jalan Siap Diterima',
                            "Surat jalan {$nomor} sudah diperiksa security dan siap untuk Anda terima.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id),
                            $roles,
                            $targetJabatan !== '' ? $targetJabatan : null
                        );
                    }
                    if ($picUserId) {
                        AppNotification::notifyUser(
                            $picUserId,
                            AppNotification::TYPE_SURAT_SIAP_TERIMA,
                            'Surat Jalan Siap Diterima',
                            "Surat jalan {$nomor} sudah diperiksa security dan siap untuk Anda terima.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id)
                        );
                    }
                    break;

                case 'DITERIMA':
                case 'SELESAI':
                    if ($previousStatus === 'DITERIMA') {
                        break;
                    }
                    // Notify operator gudang asal: surat sudah diterima
                    if ($suratJalan->gudang_asal_id) {
                        AppNotification::notifyGudangOperators(
                            $suratJalan->gudang_asal_id,
                            AppNotification::TYPE_SURAT_DITERIMA,
                            'Surat Jalan Diterima',
                            "Surat jalan {$nomor} sudah diterima oleh {$gudangTujuan}.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id),
                            ['operator_gudang']
                        );
                    }
                    break;

                case 'DITOLAK':
                    // Notify operator gudang asal: surat ditolak
                    if ($suratJalan->gudang_asal_id) {
                        AppNotification::notifyGudangOperators(
                            $suratJalan->gudang_asal_id,
                            AppNotification::TYPE_SURAT_DITOLAK,
                            'Surat Jalan Ditolak',
                            "Surat jalan {$nomor} ditolak oleh security di {$gudangTujuan}.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id),
                            ['operator_gudang']
                        );
                    }
                    break;
                case 'DITOLAK_PERSETUJUAN':
                    // Notify operator gudang asal: persetujuan ditolak
                    if ($suratJalan->gudang_asal_id) {
                        AppNotification::notifyGudangOperators(
                            $suratJalan->gudang_asal_id,
                            AppNotification::TYPE_SURAT_DITOLAK,
                            'Surat Jalan Ditolak',
                            "Persetujuan surat jalan {$nomor} ditolak. Silakan perbaiki dan ajukan ulang.",
                            $suratJalan->id,
                            route('gudang.surat-jalan.show', $suratJalan->id),
                            ['operator_gudang']
                        );
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create notification for SuratJalan: ' . $e->getMessage());
        }
    }

    protected function recordStatusHistory(SuratJalan $suratJalan): void
    {
        if (!Schema::hasTable('surat_jalan_status_histories')) {
            return;
        }

        SuratJalanStatusHistory::create([
            'surat_jalan_id' => $suratJalan->id,
            'status' => $suratJalan->status ?? 'DRAFT',
            'occurred_at' => now(),
            'actor_id' => Auth::id(),
        ]);
    }
}
