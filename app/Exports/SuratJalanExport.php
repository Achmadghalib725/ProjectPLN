<?php

namespace App\Exports;

use App\Models\SuratJalan;
use App\Models\Gudang;
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
    protected int|array|null $gudangId;
    protected ?string $tipe;
    protected ?string $tanggalMulai;
    protected ?string $tanggalSelesai;
    protected ?string $statusFilter;
    protected string $direction;
    protected ?string $sheetTitle;
    protected string $gudangLabel;
    protected int $rowNumber = 0;
    protected Carbon $exportedAt;

    public function __construct(
        int|array|null $gudangId = null,
        ?string $tipe = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null,
        ?string $statusFilter = null,
        ?string $direction = null,
        ?string $sheetTitle = null
    ) {
        $this->gudangId = $gudangId;
        $this->tipe = $tipe;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->statusFilter = $statusFilter ? strtoupper($statusFilter) : null;
        $this->direction = $this->normalizeDirection($direction);
        $this->sheetTitle = $sheetTitle;
        $this->exportedAt = Carbon::now();
        $this->gudangLabel = $this->resolveGudangLabel();
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
        $gudangIds = $this->normalizeGudangIds();
        if (!empty($gudangIds)) {
            $query->where(function ($q) use ($gudangIds) {
                if ($this->direction === 'IN') {
                    $q->whereIn('gudang_tujuan_id', $gudangIds);
                    return;
                }

                if ($this->direction === 'OUT') {
                    $q->whereIn('gudang_asal_id', $gudangIds);
                    return;
                }

                $q->whereIn('gudang_asal_id', $gudangIds)
                    ->orWhereIn('gudang_tujuan_id', $gudangIds);
            });
        }

        // Filter by type
        if ($this->tipe && $this->tipe !== 'ALL') {
            if ($this->tipe === 'PEMINJAMAN') {
                $query->whereIn('tipe', ['PEMINJAMAN', 'PENGEMBALIAN']);
            } else {
                $query->where('tipe', $this->tipe);
            }
        }

        // Filter by date range
        if ($this->tanggalMulai) {
            $query->whereDate('tanggal', '>=', $this->tanggalMulai);
        }

        if ($this->tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $this->tanggalSelesai);
        }

        $query->where('status', '!=', 'DRAFT');
        if ($this->statusFilter && $this->statusFilter !== 'ALL') {
            if ($this->statusFilter === 'SELESAI') {
                $query->where('status', 'SELESAI');
            } elseif ($this->statusFilter === 'BERLANGSUNG') {
                $query->where('status', '!=', 'SELESAI');
            }
        }

        return $query->orderByDesc('tanggal')->orderByDesc('id');
    }

    public function title(): string
    {
        $title = $this->sheetTitle ?: 'Rekap Surat Jalan';
        $title = preg_replace('/[\\\\\\/\\*\\?\\[\\]:]/', '-', $title);
        if (strlen($title) > 31) {
            $title = substr($title, 0, 31);
        }

        return $title;
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
            'Barang',
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
        $picNama = $suratJalan->picTujuan->nama
            ?? $suratJalan->pic_tujuan_custom_nama
            ?? '-';
        $picJabatan = $suratJalan->picTujuan->jabatan
            ?? $suratJalan->pic_tujuan_custom_jabatan
            ?? '-';
        $picNoHp = $suratJalan->picTujuan->no_hp
            ?? $suratJalan->pic_tujuan_custom_no_hp
            ?? '-';
        $barangList = $suratJalan->items
            ->map(fn ($detail) => $detail->item->nama ?? null)
            ->filter()
            ->unique()
            ->values()
            ->implode("\r\n");

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
            $picNama,
            $picJabatan,
            $picNoHp,
            $suratJalan->pembuat->name ?? '-',
            $waktuPembuat?->format('Y-m-d H:i:s') ?? '-',
            $waktuPenerima?->format('Y-m-d H:i:s') ?? '-',
            $this->calculateLamaPeminjaman($suratJalan),
            $suratJalan->catatan ?? '-',
            $barangList !== '' ? $barangList : '-',
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
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            // Style the header row (bold, background color)
            3 => [
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
        return 'A3';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->setCellValue('A1', 'Waktu Rekap: ' . $this->exportedAt->format('Y-m-d H:i:s'));
                $sheet->setCellValue('A2', 'Lokasi Gudang: ' . $this->gudangLabel);
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getStyle("A1:{$lastColumn}2")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:{$lastColumn}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $barangColumnIndex = array_search('Barang', $this->headings(), true);
                if ($barangColumnIndex !== false) {
                    $barangColumn = Coordinate::stringFromColumnIndex($barangColumnIndex + 1);
                    $dataStartRow = 4;
                    if ($highestRow >= $dataStartRow) {
                        $sheet->getStyle("{$barangColumn}{$dataStartRow}:{$barangColumn}{$highestRow}")
                            ->getAlignment()
                            ->setWrapText(true);
                        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                            $sheet->getRowDimension($row)->setRowHeight(-1);
                        }
                    }
                }
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
        if (!$status) {
            return '-';
        }

        return match ($status) {
            'DRAFT' => 'Draft',
            'DIKIRIM' => 'Dikirim',
            'DIPERIKSA' => 'Diperiksa',
            'DITERIMA' => 'Diterima',
            'DIKEMBALIKAN' => 'Dikembalikan',
            'DITOLAK' => 'Ditolak',
            'MENUNGGU_DIKEMBALIKAN' => 'Menunggu Dikembalikan',
            'SELESAI' => 'Selesai',
            default => $this->formatStatusFallback($status),
        };
    }

    private function formatStatusFallback(string $status): string
    {
        $text = str_replace('_', ' ', $status);
        return ucwords(strtolower($text));
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

    private function resolveGudangLabel(): string
    {
        if (!$this->gudangId) {
            $gudangNames = Gudang::query()
                ->where('kode', '!=', 'GDG-EXT')
                ->orderBy('nama')
                ->pluck('nama')
                ->filter()
                ->values()
                ->all();

            return !empty($gudangNames)
                ? implode(', ', $gudangNames)
                : 'Semua Gudang';
        }

        $gudangIds = $this->normalizeGudangIds();
        if (empty($gudangIds)) {
            $gudangNames = Gudang::query()
                ->where('kode', '!=', 'GDG-EXT')
                ->orderBy('nama')
                ->pluck('nama')
                ->filter()
                ->values()
                ->all();

            return !empty($gudangNames)
                ? implode(', ', $gudangNames)
                : 'Semua Gudang';
        }

        $gudangs = Gudang::query()
            ->whereIn('id', $gudangIds)
            ->get(['id', 'nama'])
            ->keyBy('id');

        $labels = [];
        foreach ($gudangIds as $id) {
            $gudang = $gudangs->get($id);
            if (!$gudang) {
                $labels[] = 'Gudang #' . $id;
                continue;
            }

            $labels[] = $gudang->nama;
        }

        return implode(', ', $labels);
    }

    private function normalizeGudangIds(): array
    {
        if (!$this->gudangId) {
            return [];
        }

        $gudangIds = is_array($this->gudangId) ? $this->gudangId : [$this->gudangId];
        return array_values(array_unique(array_filter($gudangIds, fn ($id) => $id !== null && $id !== '')));
    }

    private function normalizeDirection(?string $direction): string
    {
        $direction = strtoupper((string) $direction);
        return in_array($direction, ['IN', 'OUT'], true) ? $direction : 'ALL';
    }
}
