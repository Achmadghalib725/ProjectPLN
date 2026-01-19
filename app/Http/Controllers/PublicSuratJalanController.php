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

        $suratJalan = SuratJalan::with(['ttdPembuat', 'ttdPenerima', 'statusHistories'])
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
        } else {
            $nama = $suratJalan->ttdPenerima?->name;
            $waktuApproval = $resolveHistoryTime(['DITERIMA', 'SELESAI']) ?? $suratJalan->waktu_ttd_penerima;
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
