<?php

namespace App\Http\Controllers;

use App\Exports\SuratJalanMultiSheetExport;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class RekapController extends Controller
{
    /**
     * Show rekap page for Admin (all gudang data)
     */
    public function index()
    {
        $gudangs = Gudang::where('kode', '!=', 'GDG-EXT')
            ->orderBy('nama')
            ->get();

        return view('admin.rekap.index', compact('gudangs'));
    }

    /**
     * Export Surat Jalan to Excel (Admin - all gudang or filtered)
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'gudang_id' => ['nullable', 'integer', 'exists:gudangs,id'],
            'tipe' => ['nullable', 'string', Rule::in(['ALL', 'TRANSFER', 'PEMINJAMAN'])],
            'status' => ['nullable', 'string', Rule::in(['ALL', 'BERLANGSUNG', 'SELESAI'])],
            'periode' => ['nullable', 'string', Rule::in(['1_minggu', '1_bulan', '3_bulan', '6_bulan', '1_tahun', 'custom'])],
            'tanggal_mulai' => ['nullable', 'date', 'required_if:periode,custom'],
            'tanggal_selesai' => ['nullable', 'date', 'required_if:periode,custom', 'after_or_equal:tanggal_mulai'],
        ]);

        $dates = $this->calculateDateRange(
            $validated['periode'] ?? '1_bulan',
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_selesai'] ?? null
        );

        $fileName = 'rekap-surat-jalan-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new SuratJalanMultiSheetExport(
                $validated['gudang_id'] ?? null, // null = all gudang
                $validated['tipe'] ?? null,
                $dates['start'],
                $dates['end'],
                $validated['status'] ?? null
            ),
            $fileName
        );
    }

    /**
     * Calculate date range from period option
     */
    private function calculateDateRange(string $periode, ?string $tanggalMulai, ?string $tanggalSelesai): array
    {
        $now = Carbon::now();

        switch ($periode) {
            case '1_minggu':
                return [
                    'start' => $now->copy()->subWeek()->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '1_bulan':
                return [
                    'start' => $now->copy()->subMonth()->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '3_bulan':
                return [
                    'start' => $now->copy()->subMonths(3)->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '6_bulan':
                return [
                    'start' => $now->copy()->subMonths(6)->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case '1_tahun':
                return [
                    'start' => $now->copy()->subYear()->toDateString(),
                    'end' => $now->toDateString(),
                ];
            case 'custom':
                return [
                    'start' => $tanggalMulai,
                    'end' => $tanggalSelesai,
                ];
            default:
                return [
                    'start' => $now->copy()->subMonth()->toDateString(),
                    'end' => $now->toDateString(),
                ];
        }
    }
}
