<?php

namespace App\Http\Controllers;

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

        return view('security.detail', compact('suratJalan'));
    }

    /**
     * Show detail of a surat jalan
     */
    public function show($id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item'])
            ->findOrFail($id);

        return view('security.detail', compact('suratJalan'));
    }

    /**
     * Validate and update status from DIKIRIM to DITERIMA
     */
    public function terima(Request $request, $id)
    {
        $suratJalan = SuratJalan::findOrFail($id);

        // Check if status is DIKIRIM
        if ($suratJalan->status !== 'DIKIRIM') {
            return back()->with('error', 'Surat Jalan ini tidak dalam status DIKIRIM. Status saat ini: ' . $suratJalan->status);
        }

        DB::transaction(function () use ($suratJalan) {
            $suratJalan->update([
                'status' => 'DITERIMA',
            ]);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Surat Jalan ' . $suratJalan->nomor . ' berhasil dikonfirmasi sebagai DITERIMA.');
    }

    /**
     * Reject/return surat jalan (optional - jika ada masalah)
     */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $suratJalan = SuratJalan::findOrFail($id);

        if ($suratJalan->status !== 'DIKIRIM') {
            return back()->with('error', 'Surat Jalan ini tidak dalam status DIKIRIM.');
        }

        // For now, just add a note - could implement rejection logic later
        $suratJalan->update([
            'catatan' => $suratJalan->catatan . "\n[DITOLAK SECURITY: " . $request->alasan . "]",
        ]);

        return redirect()->route('dashboard')
            ->with('warning', 'Surat Jalan ' . $suratJalan->nomor . ' ditolak dengan alasan: ' . $request->alasan);
    }
}
