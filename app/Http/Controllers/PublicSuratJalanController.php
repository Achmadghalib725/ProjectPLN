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

        // Load dengan items untuk verifikasi hash dan statusHistories untuk timestamp
        $suratJalan = SuratJalan::with(['ttdPembuat', 'ttdPenerima', 'items', 'statusHistories'])
            ->where('id', $id)
            ->where('qr_token', $token)
            ->firstOrFail();
        $historyMap = $suratJalan->statusHistories?->groupBy('status') ?? collect();
        $resolveHistoryTime = function (array $statuses) use ($historyMap) {
            foreach ($statuses as $status) {
                $entry = $historyMap->get($status)?->last();
                if ($entry?->occurred_at) {
                    return $entry->occurred_at;
                }
            }
            return null;
        };

        if ($role === 'pengirim') {
            $nama = $suratJalan->ttdPembuat?->name;
            $waktuApproval = $resolveHistoryTime(['DIPERIKSA_PENGIRIM', 'DIKIRIM']) ?? $suratJalan->waktu_ttd_pembuat;
            $signatureMetadata = $suratJalan->signature_metadata_pembuat;
            $signatureHash = $suratJalan->signature_hash_pembuat;
        } else {
            $nama = $suratJalan->ttdPenerima?->name;
            $waktuApproval = $resolveHistoryTime(['DITERIMA', 'SELESAI']) ?? $suratJalan->waktu_ttd_penerima;
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
