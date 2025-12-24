<?php

namespace App\Exports;

use App\Models\SuratJalan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SuratJalanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected ?int $gudangId;
    protected ?string $tipe;
    protected ?string $tanggalMulai;
    protected ?string $tanggalSelesai;
    protected int $rowNumber = 0;

    public function __construct(
        ?int $gudangId = null,
        ?string $tipe = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ) {
        $this->gudangId = $gudangId;
        $this->tipe = $tipe;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
    }

    public function query()
    {
        $query = SuratJalan::query()
            ->with([
                'gudangAsal',
                'gudangTujuan',
                'pembuat',
                'picTujuan',
                'items.item'
            ])
            ->withCount('items')
            ->withSum('items', 'jumlah');

        // Filter by gudang (for Operator) - either as asal or tujuan
        if ($this->gudangId) {
            $query->where(function ($q) {
                $q->where('gudang_asal_id', $this->gudangId)
                    ->orWhere('gudang_tujuan_id', $this->gudangId);
            });
        }

        // Filter by type
        if ($this->tipe && $this->tipe !== 'ALL') {
            $query->where('tipe', $this->tipe);
        }

        // Filter by date range
        if ($this->tanggalMulai) {
            $query->whereDate('tanggal', '>=', $this->tanggalMulai);
        }

        if ($this->tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $this->tanggalSelesai);
        }

        return $query->orderByDesc('tanggal')->orderByDesc('id');
    }

    public function title(): string
    {
        return 'Rekap Surat Jalan';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Surat Jalan',
            'Tanggal',
            'Tipe',
            'Status',
            'Gudang Asal',
            'Gudang Tujuan',
            'Nama Driver',
            'Jenis Kendaraan',
            'Nomor Plat',
            'PIC Tujuan - Nama',
            'PIC Tujuan - Jabatan',
            'PIC Tujuan - No HP',
            'Dibuat Oleh',
            'Waktu TTD Pembuat',
            'Waktu TTD Penerima',
            'Catatan',
            'Total Jenis Item',
            'Total Jumlah Barang',
        ];
    }

    public function map($suratJalan): array
    {
        $this->rowNumber++;

        // Determine gudang tujuan name
        $gudangTujuanNama = $suratJalan->gudang_tujuan_is_custom
            ? ($suratJalan->gudang_tujuan_custom_nama ?? 'Gudang Lainnya')
            : ($suratJalan->gudangTujuan->nama ?? '-');

        return [
            $this->rowNumber,
            $suratJalan->nomor ?? '-',
            $suratJalan->tanggal?->format('Y-m-d') ?? '-',
            $this->formatTipe($suratJalan->tipe),
            $this->formatStatus($suratJalan->status),
            $suratJalan->gudangAsal->nama ?? '-',
            $gudangTujuanNama,
            $suratJalan->nama_driver ?? '-',
            $suratJalan->jenis_kendaraan ?? '-',
            $suratJalan->nomor_plat ?? '-',
            $suratJalan->picTujuan->nama ?? '-',
            $suratJalan->picTujuan->jabatan ?? '-',
            $suratJalan->picTujuan->no_hp ?? '-',
            $suratJalan->pembuat->name ?? '-',
            $suratJalan->waktu_ttd_pembuat?->format('Y-m-d H:i:s') ?? '-',
            $suratJalan->waktu_ttd_penerima?->format('Y-m-d H:i:s') ?? '-',
            $suratJalan->catatan ?? '-',
            $suratJalan->items_count ?? 0,
            $suratJalan->items_sum_jumlah ?? 0,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the header row (bold, background color)
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0066CC'],
                ],
            ],
        ];
    }

    private function formatTipe(?string $tipe): string
    {
        return match ($tipe) {
            'TRANSFER' => 'Transfer',
            'PEMINJAMAN' => 'Peminjaman',
            'PENGEMBALIAN' => 'Pengembalian',
            default => $tipe ?? '-',
        };
    }

    private function formatStatus(?string $status): string
    {
        return match ($status) {
            'DRAFT' => 'Draft',
            'DIKIRIM' => 'Dikirim',
            'DIPERIKSA' => 'Diperiksa',
            'DITERIMA' => 'Diterima',
            'DIKEMBALIKAN' => 'Dikembalikan',
            'DITOLAK' => 'Ditolak',
            'MENUNGGU_DIKEMBALIKAN' => 'Menunggu Dikembalikan',
            'SELESAI' => 'Selesai',
            default => $status ?? '-',
        };
    }
}
