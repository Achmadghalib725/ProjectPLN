<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use Illuminate\Http\Request;

class PublicSuratJalanController extends Controller
{
    public function signature(Request $request, $id, $token, $role)
    {
        if (!in_array($role, ['pengirim', 'penerima'], true)) {
            abort(404);
        }

        $suratJalan = SuratJalan::with(['ttdPembuat', 'ttdPenerima'])
            ->where('id', $id)
            ->where('qr_token', $token)
            ->firstOrFail();

        if ($role === 'pengirim') {
            $nama = $suratJalan->ttdPembuat?->name;
            $waktuApproval = $suratJalan->waktu_ttd_pembuat;
        } else {
            $nama = $suratJalan->ttdPenerima?->name;
            $waktuApproval = $suratJalan->waktu_ttd_penerima;
        }

        if (!$nama || !$waktuApproval) {
            abort(404);
        }

        return view('public.surat-jalan-signature', [
            'role' => $role,
            'suratJalan' => $suratJalan,
            'nama' => $nama,
            'waktuApproval' => $waktuApproval,
        ]);
    }
}
