<?php

namespace App\Exports;

use App\Models\Gudang;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SuratJalanMultiSheetExport implements WithMultipleSheets
{
    protected int|array|null $gudangId;
    protected ?string $tipe;
    protected ?string $tanggalMulai;
    protected ?string $tanggalSelesai;
    protected ?string $statusFilter;

    public function __construct(
        int|array|null $gudangId = null,
        ?string $tipe = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null,
        ?string $statusFilter = null
    ) {
        $this->gudangId = $gudangId;
        $this->tipe = $tipe;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->statusFilter = $statusFilter;
    }

    public function sheets(): array
    {
        $gudangIds = $this->normalizeGudangIds();
        $sheets = [];

        if (empty($gudangIds)) {
            $gudangIds = Gudang::query()
                ->where('kode', '!=', 'GDG-EXT')
                ->orderBy('nama')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if (count($gudangIds) === 0) {
            return [
                new SuratJalanExport(
                    null,
                    $this->tipe,
                    $this->tanggalMulai,
                    $this->tanggalSelesai,
                    $this->statusFilter,
                    'ALL',
                    'Semua Surat Masuk & Keluar'
                ),
            ];
        }

        if (count($gudangIds) === 1) {
            $gudangId = $gudangIds[0];
            return [
                new SuratJalanExport(
                    $gudangId,
                    $this->tipe,
                    $this->tanggalMulai,
                    $this->tanggalSelesai,
                    $this->statusFilter,
                    'ALL',
                    'Semua Surat Masuk & Keluar'
                ),
                new SuratJalanExport(
                    $gudangId,
                    $this->tipe,
                    $this->tanggalMulai,
                    $this->tanggalSelesai,
                    $this->statusFilter,
                    'IN',
                    'Surat Masuk'
                ),
                new SuratJalanExport(
                    $gudangId,
                    $this->tipe,
                    $this->tanggalMulai,
                    $this->tanggalSelesai,
                    $this->statusFilter,
                    'OUT',
                    'Surat Keluar'
                ),
            ];
        }

        $sheets[] = new SuratJalanExport(
            $gudangIds,
            $this->tipe,
            $this->tanggalMulai,
            $this->tanggalSelesai,
            $this->statusFilter,
            'ALL',
            'Semua Surat Masuk & Keluar'
        );

        $gudangMap = Gudang::query()
            ->whereIn('id', $gudangIds)
            ->get(['id', 'nama'])
            ->keyBy('id');

        foreach ($gudangIds as $gudangId) {
            $gudangName = $gudangMap->get($gudangId)?->nama ?? ('Gudang ' . $gudangId);
            $sheets[] = new SuratJalanExport(
                $gudangId,
                $this->tipe,
                $this->tanggalMulai,
                $this->tanggalSelesai,
                $this->statusFilter,
                'IN',
                'Surat Masuk - ' . $gudangName
            );
            $sheets[] = new SuratJalanExport(
                $gudangId,
                $this->tipe,
                $this->tanggalMulai,
                $this->tanggalSelesai,
                $this->statusFilter,
                'OUT',
                'Surat Keluar - ' . $gudangName
            );
        }

        return $sheets;
    }

    private function normalizeGudangIds(): array
    {
        if (!$this->gudangId) {
            return [];
        }

        $gudangIds = is_array($this->gudangId) ? $this->gudangId : [$this->gudangId];
        $gudangIds = array_filter($gudangIds, fn ($id) => $id !== null && $id !== '');
        return array_values(array_unique(array_map('intval', $gudangIds)));
    }
}
