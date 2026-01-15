<?php

namespace App\Exports;

use App\Models\SuratJalan;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SuratJalanExport implements FromQuery, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected ?int $gudangId;
    protected ?string $tipe;
    protected ?string $tanggalMulai;
    protected ?string $tanggalSelesai;
    protected int $rowNumber = 0;
    protected Carbon $exportedAt;

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
        $this->exportedAt = Carbon::now();
    }

    public function query()
    {
        $query = SuratJalan::query()
            ->with([
                'gudangAsal',
                'gudangTujuan',
                'pembuat',
                'picTujuan',
                'items.item',
                'peminjaman',
                'statusHistories'
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
            'Lama Peminjaman',
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
        $historyMap = $suratJalan->statusHistories?->groupBy('status') ?? collect();
        $waktuPembuat = $this->historyTime($historyMap, ['DIPERIKSA_PENGIRIM', 'DIKIRIM'])
            ?? $suratJalan->waktu_ttd_pembuat;
        $waktuPenerima = $this->historyTime($historyMap, ['DITERIMA', 'SELESAI'])
            ?? $suratJalan->waktu_ttd_penerima;

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
            $waktuPembuat?->format('Y-m-d H:i:s') ?? '-',
            $waktuPenerima?->format('Y-m-d H:i:s') ?? '-',
            $this->calculateLamaPeminjaman($suratJalan),
            $suratJalan->catatan ?? '-',
            $suratJalan->items_count ?? 0,
            $suratJalan->items_sum_jumlah ?? 0,
        ];
    }

    /**
     * Calculate loan duration for PEMINJAMAN type
     */
    private function calculateLamaPeminjaman($suratJalan): string
    {
        // Only calculate for PEMINJAMAN type
        if ($suratJalan->tipe !== 'PEMINJAMAN') {
            return '-';
        }

        $peminjaman = $suratJalan->peminjaman;

        // If no peminjaman relation found
        if (!$peminjaman) {
            return '-';
        }

        // Determine start time (when items were received)
        $startTime = $peminjaman->waktu_diterima;

        if (!$startTime) {
            return 'Belum diterima';
        }

        // Determine end time (when items were returned, or now if not yet returned)
        $endTime = $peminjaman->waktu_pengembalian;

        if ($endTime) {
            // Already returned - calculate duration
            $duration = $startTime->diff($endTime);
        } else {
            // Not yet returned - calculate from start to now
            $duration = $startTime->diff(Carbon::now());
        }

        // Format the duration
        $parts = [];

        if ($duration->y > 0) {
            $parts[] = $duration->y . ' tahun';
        }
        if ($duration->m > 0) {
            $parts[] = $duration->m . ' bulan';
        }
        if ($duration->d > 0) {
            $parts[] = $duration->d . ' hari';
        }

        if (empty($parts)) {
            // Less than a day
            if ($duration->h > 0) {
                $parts[] = $duration->h . ' jam';
            }
            if ($duration->i > 0) {
                $parts[] = $duration->i . ' menit';
            }
        }

        $durationText = !empty($parts) ? implode(' ', $parts) : 'Kurang dari 1 menit';

        // Add suffix if not yet returned
        if (!$endTime) {
            $durationText .= ' (berlangsung)';
        }

        return $durationText;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            // Style the header row (bold, background color)
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0066CC'],
                ],
            ],
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', 'Waktu Rekap: ' . $this->exportedAt->format('Y-m-d H:i:s'));
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getStyle("A1:{$lastColumn}1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            },
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

    private function historyTime($historyMap, array $statuses): ?Carbon
    {
        foreach ($statuses as $status) {
            $entry = $historyMap->get($status)?->last();
            if ($entry?->occurred_at) {
                return $entry->occurred_at instanceof Carbon
                    ? $entry->occurred_at
                    : Carbon::parse($entry->occurred_at);
            }
        }

        return null;
    }
}
