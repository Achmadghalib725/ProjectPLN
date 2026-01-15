<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pic;
use Illuminate\Support\Str;

class SuratJalan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'waktu_ttd_pembuat' => 'datetime',
        'waktu_ttd_penerima' => 'datetime',
        'gudang_tujuan_is_custom' => 'boolean',
        'signature_metadata_pembuat' => 'array',
        'signature_metadata_penerima' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($suratJalan) {
            if (empty($suratJalan->qr_token)) {
                $suratJalan->qr_token = Str::random(40);
            }
        });
    }

    // Relasi ke Item Detail
    public function items()
    {
        return $this->hasMany(SuratJalanItem::class);
    }

    public function gudangAsal()
    {
        return $this->belongsTo(Gudang::class, 'gudang_asal_id');
    }

    public function gudangTujuan()
    {
        return $this->belongsTo(Gudang::class, 'gudang_tujuan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function picTujuan()
    {
        return $this->belongsTo(Pic::class, 'pic_tujuan_id');
    }

    public function ttdPembuat()
    {
        return $this->belongsTo(User::class, 'ttd_pembuat_id');
    }

    public function ttdPenerima()
    {
        return $this->belongsTo(User::class, 'ttd_penerima_id');
    }

    public function attachments()
    {
        return $this->hasMany(SuratJalanAttachment::class);
    }

    /**
     * Relasi untuk surat jalan tipe PEMINJAMAN
     * Mendapatkan record peminjaman yang terkait
     */
    public function peminjaman()
    {
        return $this->hasOne(Peminjaman::class, 'surat_jalan_kirim_id');
    }

    /**
     * Relasi untuk surat jalan tipe PENGEMBALIAN
     * Mendapatkan record peminjaman yang dikembalikan
     */
    public function peminjamanKembali()
    {
        return $this->hasOne(Peminjaman::class, 'surat_jalan_kembali_id');
    }

    /**
     * Generate hash SHA256 dari data dokumen untuk tanda tangan elektronik.
     * Hash ini akan digunakan untuk memverifikasi integritas dokumen.
     *
     * @param string $role 'pembuat' atau 'penerima'
     * @return string Hash SHA256 (64 karakter hex)
     */
    public function generateDocumentHash(string $role = 'pembuat'): string
    {
        // Data inti dokumen yang tidak boleh berubah setelah ditandatangani
        $data = [
            'nomor' => $this->nomor,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'tipe' => $this->tipe,
            'gudang_asal_id' => $this->gudang_asal_id,
            'gudang_tujuan_id' => $this->gudang_tujuan_id,
            'gudang_tujuan_is_custom' => $this->gudang_tujuan_is_custom,
            'gudang_tujuan_custom_nama' => $this->gudang_tujuan_custom_nama,
            'pic_tujuan_id' => $this->pic_tujuan_id,
            'catatan' => $this->catatan,
        ];

        // Tambahkan data items (sorted by item_id untuk konsistensi)
        $items = $this->items()->orderBy('item_id')->get()->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'jumlah' => $item->jumlah,
                'keterangan' => $item->keterangan,
            ];
        })->toArray();
        $data['items'] = $items;

        // Tambahkan data TTD pembuat
        $data['ttd_pembuat_id'] = $this->ttd_pembuat_id;
        $data['waktu_ttd_pembuat'] = $this->waktu_ttd_pembuat?->format('Y-m-d H:i:s');

        // Jika role penerima, tambahkan juga data TTD penerima
        if ($role === 'penerima') {
            $data['ttd_penerima_id'] = $this->ttd_penerima_id;
            $data['waktu_ttd_penerima'] = $this->waktu_ttd_penerima?->format('Y-m-d H:i:s');
        }

        // Sort keys untuk konsistensi hash
        ksort($data);

        // Generate SHA256 hash
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Verifikasi apakah hash signature masih valid (dokumen belum dimodifikasi).
     *
     * @param string $role 'pembuat' atau 'penerima'
     * @return bool|null True jika valid, False jika tidak valid, Null jika belum ada signature
     */
    public function verifySignatureHash(string $role = 'pembuat'): ?bool
    {
        $storedHash = $role === 'penerima'
            ? $this->signature_hash_penerima
            : $this->signature_hash_pembuat;

        if (!$storedHash) {
            return null;
        }

        $currentHash = $this->generateDocumentHash($role);

        return hash_equals($storedHash, $currentHash);
    }

    /**
     * Generate metadata signature untuk audit trail.
     *
     * @return array
     */
    public static function generateSignatureMetadata(): array
    {
        return [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'signed_at' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
        ];
    }
}
