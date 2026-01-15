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

        // Load dengan items untuk verifikasi hash
        $suratJalan = SuratJalan::with(['ttdPembuat', 'ttdPenerima', 'items'])
            ->where('id', $id)
            ->where('qr_token', $token)
            ->firstOrFail();

        if ($role === 'pengirim') {
            $nama = $suratJalan->ttdPembuat?->name;
            $waktuApproval = $suratJalan->waktu_ttd_pembuat;
            $signatureMetadata = $suratJalan->signature_metadata_pembuat;
            $signatureHash = $suratJalan->signature_hash_pembuat;
        } else {
            $nama = $suratJalan->ttdPenerima?->name;
            $waktuApproval = $suratJalan->waktu_ttd_penerima;
            $signatureMetadata = $suratJalan->signature_metadata_penerima;
            $signatureHash = $suratJalan->signature_hash_penerima;
        }

        if (!$nama || !$waktuApproval) {
            abort(404);
        }

        // Verifikasi integritas dokumen menggunakan hash
        $roleHash = $role === 'pengirim' ? 'pembuat' : 'penerima';
        $isHashValid = $suratJalan->verifySignatureHash($roleHash);

        // Mode audit untuk melihat detail teknis (tambahkan ?audit=1 di URL)
        $isAuditMode = $request->query('audit') === '1';

        return view('public.surat-jalan-signature', [
            'role' => $role,
            'suratJalan' => $suratJalan,
            'nama' => $nama,
            'waktuApproval' => $waktuApproval,
            'isHashValid' => $isHashValid,
            'signatureMetadata' => $signatureMetadata,
            'signatureHash' => $signatureHash,
            'isAuditMode' => $isAuditMode,
        ]);
    }
}
