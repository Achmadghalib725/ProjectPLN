<?php

namespace App\Events;

use App\Models\Peminjaman;
use App\Models\SuratJalan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratJalanStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public bool $afterCommit = true;
    public string $action;

    public int $suratJalanId;
    public string $status;
    public ?string $peminjamanStatus;
    public ?int $gudangAsalId;
    public ?int $gudangTujuanId;
    public ?string $updatedAt;

    public function __construct(SuratJalan $suratJalan, string $action = 'status_updated')
    {
        $suratJalan->refresh();

        $this->action = $action;
        $this->suratJalanId = $suratJalan->id;
        $this->status = $suratJalan->status ?? 'DRAFT';
        $this->gudangAsalId = $suratJalan->gudang_asal_id;
        $this->gudangTujuanId = $suratJalan->gudang_tujuan_id;
        $this->updatedAt = $suratJalan->updated_at?->toIso8601String();
        $this->peminjamanStatus = $this->resolvePeminjamanStatus($suratJalan);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel("surat-jalan.detail.{$this->suratJalanId}"),
        ];

        if ($this->gudangAsalId) {
            $channels[] = new Channel("surat-jalan.gudang.{$this->gudangAsalId}");
        }

        if ($this->gudangTujuanId) {
            $channels[] = new Channel("surat-jalan.gudang.{$this->gudangTujuanId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'SuratJalanStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->suratJalanId,
            'action' => $this->action,
            'status' => $this->status,
            'peminjaman_status' => $this->peminjamanStatus,
            'gudang_asal_id' => $this->gudangAsalId,
            'gudang_tujuan_id' => $this->gudangTujuanId,
            'updated_at' => $this->updatedAt,
        ];
    }

    private function resolvePeminjamanStatus(SuratJalan $suratJalan): ?string
    {
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            return Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->value('status');
        }

        if ($suratJalan->tipe === 'PENGEMBALIAN') {
            return Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->value('status');
        }

        return null;
    }
}
