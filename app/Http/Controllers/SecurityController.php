<?php

namespace App\Http\Controllers;

use App\Models\ItemStock;
use App\Models\Peminjaman;
use App\Models\StockMovement;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

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

        $nomorLower = strtolower($nomor);
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item', 'statusHistories.actor'])
            ->whereRaw('LOWER(nomor) LIKE ?', ['%' . $nomorLower . '%'])
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
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items',
                    'suratJalanPengembalians',
                ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items',
                    'suratJalanPengembalians',
                ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
            }

        return view('security.detail', compact('suratJalan', 'peminjaman'));
    }

    /**
     * Show detail of a surat jalan
     */
    public function show($id)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item', 'pembuat', 'statusHistories.actor'])
            ->findOrFail($id);

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items',
                    'suratJalanPengembalians',
                ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items',
                    'suratJalanPengembalians',
                ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
            }

        return view('security.detail', compact('suratJalan', 'peminjaman'));
    }

    /**
     * Show detail of a surat jalan by QR token
     */
    public function showByToken($id, $token)
    {
        $suratJalan = SuratJalan::with(['gudangAsal', 'gudangTujuan', 'picTujuan', 'items.item', 'pembuat', 'statusHistories.actor'])
            ->where('id', $id)
            ->where('qr_token', $token)
            ->firstOrFail();

        $peminjaman = null;
        if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items',
                    'suratJalanPengembalians',
                ])->where('surat_jalan_kirim_id', $suratJalan->id)->first();
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::with([
                    'suratJalanKirim.gudangAsal',
                    'suratJalanKirim.gudangTujuan',
                    'suratJalanKirim.pembuat',
                    'suratJalanKirim.statusHistories.actor',
                    'suratJalanKembali.gudangAsal',
                    'suratJalanKembali.gudangTujuan',
                    'suratJalanKembali.pembuat',
                    'suratJalanKembali.statusHistories.actor',
                    'gudangPeminjam',
                    'gudangPemilik',
                    'items',
                    'suratJalanPengembalians',
                ])->where('surat_jalan_kembali_id', $suratJalan->id)->first();
            }

        return view('security.detail', compact('suratJalan', 'peminjaman'));
    }

    /**
     * Security approve - update status based on pemeriksaan tahap pengirim/penerima.
     */
    public function terima(Request $request, $id)
    {
        $suratJalan = SuratJalan::with(['items.item', 'gudangAsal'])->findOrFail($id);

        $request->validate([
            'checked_items' => ['array'],
            'checked_items.*' => [
                'integer',
                Rule::exists('surat_jalan_items', 'id')->where(function ($query) use ($suratJalan) {
                    $query->where('surat_jalan_id', $suratJalan->id);
                }),
            ],
        ]);

        $user = Auth::user();
        $currentStatus = $suratJalan->status;
        $senderStatuses = ['DIPERIKSA_PENGIRIM'];
        $receiverStatuses = ['DIKIRIM', 'DIKEMBALIKAN'];
        $rejectTag = 'DITOLAK';

        if (in_array($currentStatus, $senderStatuses, true)) {
            $checkStage = 'pengirim';
            $expectedGudangId = $suratJalan->gudang_asal_id;
        } elseif (in_array($currentStatus, $receiverStatuses, true)) {
            $checkStage = 'penerima';
            $expectedGudangId = $suratJalan->gudang_tujuan_id;
            if ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman?->gudang_pemilik_id) {
                    $expectedGudangId = $peminjaman->gudang_pemilik_id;
                }
            }
        } else {
            return back()->with('error', 'Surat Jalan ini tidak dalam status yang dapat diperiksa. Status saat ini: ' . $currentStatus);
        }

        if ($user->gudang_id !== $expectedGudangId) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengkonfirmasi surat jalan ini. Surat jalan ini ditujukan ke gudang lain.');
        }

        $checkedIds = collect($request->input('checked_items', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $totalItems = $suratJalan->items->count();
        if ($totalItems > 0 && count($checkedIds) !== $totalItems) {
            return back()->with('error', 'Semua item harus ditandai sesuai sebelum konfirmasi pemeriksaan.');
        }
        $checkedLookup = array_flip($checkedIds);
        $checkerId = Auth::id();
        $checkedAt = now();

        DB::transaction(function () use ($suratJalan, $checkedLookup, $checkerId, $checkedAt, $checkStage) {
            SuratJalanItem::where('surat_jalan_id', $suratJalan->id)
                ->get()
                ->each(function ($item) use ($checkedLookup, $checkerId, $checkedAt) {
                    $item->update([
                        'checked_by_security' => array_key_exists($item->id, $checkedLookup),
                        'checked_by_user_id' => $checkerId,
                        'checked_at' => $checkedAt,
                    ]);
                });

            if ($checkStage === 'pengirim') {
                if ($suratJalan->tipe === 'PENGEMBALIAN') {
                    $nextStatus = 'DIKEMBALIKAN';
                } elseif ($suratJalan->tipe === 'PEMINJAMAN' && $suratJalan->gudang_tujuan_is_custom) {
                    $nextStatus = 'MENUNGGU_DIKEMBALIKAN';
                } elseif ($suratJalan->tipe === 'TRANSFER' && $suratJalan->gudang_tujuan_is_custom) {
                    $nextStatus = 'SELESAI';
                } else {
                    $nextStatus = 'DIKIRIM';
                }
            } else {
                $nextStatus = 'DIPERIKSA_PENERIMA';
            }
            $suratJalan->update([
                'status' => $nextStatus,
            ]);

            if ($checkStage === 'pengirim') {
                if ($suratJalan->tipe === 'PEMINJAMAN') {
                    $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                    if ($peminjaman) {
                        $peminjaman->update([
                            'status' => $nextStatus === 'MENUNGGU_DIKEMBALIKAN' ? 'MENUNGGU_DIKEMBALIKAN' : 'DIKIRIM',
                            'waktu_kirim' => now(),
                        ]);
                    }
                } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                    $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                    if ($peminjaman) {
                        $peminjaman->update([
                            'status' => 'DIKEMBALIKAN',
                            'waktu_pengembalian' => now(),
                        ]);

                        // Sync status surat jalan peminjaman (kirim)
                        if ($peminjaman->surat_jalan_kirim_id) {
                            $suratJalanKirim = SuratJalan::find($peminjaman->surat_jalan_kirim_id);
                            if ($suratJalanKirim) {
                                $suratJalanKirim->update(['status' => 'DIKEMBALIKAN']);
                            }
                        }
                    }
                }

                return;
            }

            // Update peminjaman status if applicable (pemeriksaan penerima)
            if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman && $peminjaman->status === 'DIKIRIM') {
                    $peminjaman->update(['status' => 'DIPERIKSA']);
                }
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman && $peminjaman->status === 'DIKEMBALIKAN') {
                    $peminjaman->update(['status' => 'DIPERIKSA']);
                    // Note: JANGAN sync status surat jalan peminjaman (kirim) ke DIPERIKSA
                    // karena akan memicu tombol "Terima Barang" dan notifikasi ke operator gudang tujuan (peminjam)
                    // yang seharusnya tidak bisa menerima barang pengembalian
                    // Status surat jalan peminjaman tetap DIKEMBALIKAN sampai proses pengembalian selesai
                }
            }
        });

        $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);
        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        // Bump cache untuk surat peminjaman juga jika PENGEMBALIAN
        if ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
            if ($peminjaman?->surat_jalan_kirim_id) {
                $this->bumpSuratJalanDetailCacheVersion($peminjaman->surat_jalan_kirim_id);
            }
        }

        $successMessage = $checkStage === 'pengirim'
            ? 'Surat Jalan ' . $suratJalan->nomor . ' berhasil diperiksa oleh security pengirim.'
            : 'Surat Jalan ' . $suratJalan->nomor . ' berhasil diperiksa oleh security penerima.';

        return redirect()->route('dashboard')
            ->with('success', $successMessage);
    }

    /**
     * Reject surat jalan - change status to DITOLAK
     * Can reject from status menunggu pemeriksaan atau setelah dikirim
     */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $suratJalan = SuratJalan::findOrFail($id);

        $user = Auth::user();
        $currentStatus = $suratJalan->status;
        $senderStatuses = ['DIPERIKSA_PENGIRIM'];
        $receiverStatuses = ['DIKIRIM', 'DIKEMBALIKAN'];

        if (in_array($currentStatus, $senderStatuses, true)) {
            $expectedGudangId = $suratJalan->gudang_asal_id;
            $rejectTag = 'DITOLAK_PENGIRIM';
        } elseif (in_array($currentStatus, $receiverStatuses, true)) {
            $expectedGudangId = $suratJalan->gudang_tujuan_id;
            $rejectTag = 'DITOLAK_PENERIMA';
            if ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
                if ($peminjaman?->gudang_pemilik_id) {
                    $expectedGudangId = $peminjaman->gudang_pemilik_id;
                }
            }
        } else {
            return back()->with('error', 'Surat Jalan ini tidak dapat ditolak. Status saat ini: ' . $currentStatus);
        }

        if ($user->gudang_id !== $expectedGudangId) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak surat jalan ini. Surat jalan ini ditujukan ke gudang lain.');
        }

        DB::transaction(function () use ($suratJalan, $request, $rejectTag) {
            // Reset TTD dan signature hash karena surat ditolak dan perlu di-approve ulang
            $suratJalan->update([
                'status' => 'DITOLAK',
                'catatan_penolakan' => "[{$rejectTag}: " . $request->alasan . "]",
                'ttd_pembuat_id' => null,
                'waktu_ttd_pembuat' => null,
                'signature_hash_pembuat' => null,
                'signature_metadata_pembuat' => null,
                'ttd_penerima_id' => null,
                'waktu_ttd_penerima' => null,
                'signature_hash_penerima' => null,
                'signature_metadata_penerima' => null,
            ]);

            // Restore stock to gudang_asal for all types (TRANSFER, PEMINJAMAN, PENGEMBALIAN)
            // When rejected, items go back to the sender
            $itemTotals = $suratJalan->items
                ->groupBy('item_id')
                ->map(fn ($rows) => $rows->sum('jumlah'));

            $keterangan = $suratJalan->tipe === 'PENGEMBALIAN'
                ? "Pengembalian stok karena surat pengembalian ditolak ({$suratJalan->nomor})"
                : "Pengembalian stok karena surat jalan ditolak ({$suratJalan->nomor})";

            foreach ($itemTotals as $itemId => $qty) {
                $stock = ItemStock::firstOrCreate(
                    ['gudang_id' => $suratJalan->gudang_asal_id, 'item_id' => $itemId],
                    ['jumlah' => 0, 'stok_minimum' => 0]
                );

                $stokSebelum = $stock->jumlah;
                $stokSesudah = $stokSebelum + $qty;

                $stock->increment('jumlah', $qty);

                StockMovement::create([
                    'item_id' => $itemId,
                    'gudang_id' => $suratJalan->gudang_asal_id,
                    'tipe' => 'IN',
                    'jumlah' => $qty,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_type' => 'SuratJalan',
                    'referensi_id' => $suratJalan->id,
                    'created_by' => Auth::id(),
                    'keterangan' => $keterangan,
                ]);
            }

            // Update peminjaman status if applicable
            if ($suratJalan->tipe === 'PEMINJAMAN') {
                $peminjaman = Peminjaman::where('surat_jalan_kirim_id', $suratJalan->id)->first();
                if ($peminjaman) {
                    $peminjaman->update(['status' => 'DITOLAK']);
                }
            } elseif ($suratJalan->tipe === 'PENGEMBALIAN') {
                $peminjaman = Peminjaman::with('items')
                    ->where('surat_jalan_kembali_id', $suratJalan->id)
                    ->first();
                if ($peminjaman) {
                    $peminjaman->update([
                        'status' => 'DITERIMA',
                        'waktu_pengembalian' => null,
                    ]);

                    $returnTotals = $suratJalan->items
                        ->groupBy('item_id')
                        ->map(fn ($rows) => (int) $rows->sum('jumlah'));

                    $itemsByItemId = $peminjaman->items->keyBy('item_id');
                    foreach ($returnTotals as $itemId => $qty) {
                        $peminjamanItem = $itemsByItemId->get((int) $itemId);
                        if (!$peminjamanItem) {
                            continue;
                        }
                        $returned = (int) ($peminjamanItem->jumlah_dikembalikan ?? 0);
                        $newReturned = max(0, $returned - (int) $qty);
                        if ($newReturned !== $returned) {
                            $peminjamanItem->update(['jumlah_dikembalikan' => $newReturned]);
                        }
                    }

                    // Sync status surat jalan peminjaman (kirim)
                    if ($peminjaman->surat_jalan_kirim_id) {
                        $suratJalanKirim = SuratJalan::find($peminjaman->surat_jalan_kirim_id);
                        if ($suratJalanKirim && $suratJalanKirim->status === 'DIKEMBALIKAN') {
                            $suratJalanKirim->update(['status' => 'DITERIMA']);
                        }
                    }
                }
            }
        });

        $this->bumpSuratJalanCacheVersion([$suratJalan->gudang_asal_id, $suratJalan->gudang_tujuan_id]);

        // Bump cache untuk surat peminjaman juga jika PENGEMBALIAN
        if ($suratJalan->tipe === 'PENGEMBALIAN') {
            $peminjaman = Peminjaman::where('surat_jalan_kembali_id', $suratJalan->id)->first();
            if ($peminjaman?->surat_jalan_kirim_id) {
                $this->bumpSuratJalanDetailCacheVersion($peminjaman->surat_jalan_kirim_id);
            }
        }
        $this->bumpSuratJalanDetailCacheVersion($suratJalan->id);

        return redirect()->route('dashboard')
            ->with('warning', 'Surat Jalan ' . $suratJalan->nomor . ' telah DITOLAK dengan alasan: ' . $request->alasan);
    }

    private function bumpSuratJalanCacheVersion(array $gudangIds): void
    {
        $uniqueIds = collect($gudangIds)
            ->filter(fn ($id) => !empty($id))
            ->unique()
            ->values();

        foreach ($uniqueIds as $gudangId) {
            $key = "surat_jalan.version." . (int) $gudangId;
            $updated = Cache::increment($key);
            if ($updated === false) {
                Cache::forever($key, 2);
            }
        }
    }

    private function bumpSuratJalanDetailCacheVersion(int $suratJalanId): void
    {
        $key = "surat_jalan.detail.version." . (int) $suratJalanId;
        $updated = Cache::increment($key);
        if ($updated === false) {
            Cache::forever($key, 2);
        }
    }
}
