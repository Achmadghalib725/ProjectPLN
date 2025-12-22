<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    /**
     * Search surat jalan by nomor
     */
    public function search(Request $request)
    {
        $request->validate([
            'nomor' => 'required|string|min:3',
        ]);

        $nomor = $request->input('nomor');

        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item'])
            ->where('nomor', 'like', '%' . $nomor . '%')
            ->first();

        if (!$suratJalan) {
            return redirect()->route('dashboard')->with('error', 'Surat Jalan dengan nomor "' . $nomor . '" tidak ditemukan.');
        }

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::with([
                'suratJalanKirim.gudangAsal',
                'suratJalanKirim.gudangTujuan',
                'suratJalanKirim.pembuat',
                'suratJalanKembali.gudangAsal',
                'suratJalanKembali.gudangTujuan',
                'suratJalanKembali.pembuat',
                'gudangPeminjam',
                'gudangPemilik',
            ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::with([
                'suratJalanKirim.gudangAsal',
                'suratJalanKirim.gudangTujuan',
                'suratJalanKirim.pembuat',
                'suratJalanKembali.gudangAsal',
                'suratJalanKembali.gudangTujuan',
                'suratJalanKembali.pembuat',
                'gudangPeminjam',
                'gudangPemilik',
            ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
        }

        return view('security.detail', compact('suratJalan', 'peminjaman'));
    }

    /**
     * Show detail of a surat jalan
     */
    public function show($id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item', 'pembuat'])
            ->findOrFail($id);

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
            $peminjaman = Peminjaman::with([
                'suratJalanKirim.gudangAsal',
                'suratJalanKirim.gudangTujuan',
                'suratJalanKirim.pembuat',
                'suratJalanKembali.gudangAsal',
                'suratJalanKembali.gudangTujuan',
                'suratJalanKembali.pembuat',
                'gudangPeminjam',
                'gudangPemilik',
            ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
        } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::with([
                'suratJalanKirim.gudangAsal',
                'suratJalanKirim.gudangTujuan',
                'suratJalanKirim.pembuat',
                'suratJalanKembali.gudangAsal',
                'suratJalanKembali.gudangTujuan',
                'suratJalanKembali.pembuat',
                'gudangPeminjam',
                'gudangPemilik',
            ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
        }

        return view('security.detail', compact('suratJalan', 'peminjaman'));
    }

    /**
     * Show detail of a surat jalan by QR token
     */
    public function showByToken($id, $token)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item'])
            ->where('id', $id)
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('security.detail', compact('suratJalan'));
    }

    /**
     * Security approve - change status to DIPERIKSA
     * For DIKIRIM (peminjaman/transfer) -> DIPERIKSA
     * For DIKEMBALIKAN (pengembalian) -> DIPERIKSA
     */
    public function terima(Request $request, $id)
    {
        $suratJalan = SuratJalan::findOrFail($id);

        // Check valid status for security approval
        $validStatuses = ['DIKIRIM', 'DIKEMBALIKAN'];
        if (!in_array($suratJalan->status, $validStatuses)) {
            return back()->with('error', 'Surat Jalan ini tidak dalam status yang dapat diperiksa. Status saat ini: ' . $suratJalan->status);
        }

        DB::transaction(function () use ($suratJalan) {
            $suratJalan->update([
                'status' => 'DIPERIKSA',
            ]);

            // Update peminjaman status if applicable
            if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman && $peminjaman->status === 'DIKIRIM') {
                    $peminjaman->update(['status' => 'DIPERIKSA']);
                }
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman && $peminjaman->status === 'DIKEMBALIKAN') {
                    $peminjaman->update(['status' => 'DIPERIKSA']);
                }
            }
        });

        return redirect()->route('dashboard')
            ->with('success', 'Surat Jalan ' . $suratJalan->nomor . ' berhasil diperiksa dan dikonfirmasi.');
    }

    /**
     * Reject surat jalan - change status to DITOLAK
     * Can reject from DIKIRIM or DIKEMBALIKAN status
     */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $suratJalan = SuratJalan::findOrFail($id);

        // Check valid status for rejection
        $validStatuses = ['DIKIRIM', 'DIKEMBALIKAN'];
        if (!in_array($suratJalan->status, $validStatuses)) {
            return back()->with('error', 'Surat Jalan ini tidak dapat ditolak. Status saat ini: ' . $suratJalan->status);
        }

        DB::transaction(function () use ($suratJalan, $request) {
            $suratJalan->update([
                'status' => 'DITOLAK',
                'catatan' => ($suratJalan->catatan ? $suratJalan->catatan . "\n" : '') . "[DITOLAK: " . $request->alasan . "]",
            ]);

            // Update peminjaman status if applicable
            if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman) {
                    $peminjaman->update(['status' => 'DITOLAK']);
                }
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman) {
                    $peminjaman->update(['status' => 'DITOLAK']);
                }
            }
        });

        return redirect()->route('dashboard')
            ->with('warning', 'Surat Jalan ' . $suratJalan->nomor . ' telah DITOLAK dengan alasan: ' . $request->alasan);
    }
}
