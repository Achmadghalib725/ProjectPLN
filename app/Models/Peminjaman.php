<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $guarded = ['id'];

    protected $casts = [
        'waktu_pengajuan' => 'datetime',
        'waktu_kirim' => 'datetime',
        'waktu_diterima' => 'datetime',
        'waktu_pengembalian' => 'datetime',
        'waktu_selesai' => 'datetime',
        'waktu_ttd_pengirim' => 'datetime',
        'waktu_ttd_penerima' => 'datetime',
        'waktu_ttd_pengembalian' => 'datetime',
        'waktu_ttd_terima_kembali' => 'datetime',
    ];

    // Relasi ke Item Detail Peminjaman
    public function items()
    {
        return $this->hasMany(PeminjamanItem::class);
    }

    public function gudangPeminjam()
    {
        return $this->belongsTo(Gudang::class, 'gudang_peminjam_id');
    }

    public function gudangPemilik()
    {
        return $this->belongsTo(Gudang::class, 'gudang_pemilik_id');
    }

    public function suratJalanKirim()
    {
        return $this->belongsTo(SuratJalan::class, 'surat_jalan_kirim_id');
    }

    public function suratJalanKembali()
    {
        return $this->belongsTo(SuratJalan::class, 'surat_jalan_kembali_id');
    }
}
