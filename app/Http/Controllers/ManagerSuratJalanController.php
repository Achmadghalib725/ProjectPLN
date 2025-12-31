<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Peminjaman;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManagerSuratJalanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gudangIds = $user->managedGudangs()->pluck('gudangs.id')->all();

        if (empty($gudangIds)) {
            abort(403, 'Manager belum memiliki gudang yang ditugaskan');
        }

        $filters = $request->only(['search', 'status', 'tipe', 'gudang_id', 'order_by']);
        $orderBy = $filters['order_by'] ?? 'terbaru';
        $direction = $orderBy === 'terlama' ? 'asc' : 'desc';
        $statusFilter = $filters['status'] ?? 'MENUNGGU_PERSETUJUAN';

        $query = SuratJalan::query()
            ->with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan'])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->whereIn('gudang_asal_id', $gudangIds)
            ->orderBy('tanggal', $direction)
            ->orderBy('id', $direction);

        if (!empty($filters['gudang_id'])) {
            $query->where('gudang_asal_id', (int) $filters['gudang_id']);
        }

        if (!empty($filters['search'])) {
            $searchLower = strtolower($filters['search']);
            $query->whereRaw('LOWER(nomor) LIKE ?', ['%' . $searchLower . '%']);
        }

        if (!empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (!empty($statusFilter) && $statusFilter !== 'ALL') {
            $query->where('status', $statusFilter);
        }

        $suratJalans = $query->paginate(20)->appends($request->all());

        $gudangs = Schema::hasTable('gudangs')
            ? Gudang::query()->whereIn('id', $gudangIds)->orderBy('nama')->get()
            : collect();

        $stats = Schema::hasTable('surat_jalans')
            ? SuratJalan::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->whereIn('gudang_asal_id', $gudangIds)
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        return view('manager.surat-jalan.index', compact('suratJalans', 'gudangs', 'filters', 'stats'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $gudangIds = $user->managedGudangs()->pluck('gudangs.id')->all();

        if (empty($gudangIds)) {
            abort(403, 'Manager belum memiliki gudang yang ditugaskan');
        }

        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'pembuat', 'picTujuan', 'ttdPembuat', 'items.item', 'attachments'])
            ->findOrFail($id);

        if (!in_array($suratJalan->gudang_asal_id, $gudangIds, true)) {
            abort(403, 'Anda tidak berhak mengakses surat jalan gudang lain.');
        }

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
        }

        return view('manager.surat-jalan.show', compact('suratJalan', 'peminjaman'));
    }
}
