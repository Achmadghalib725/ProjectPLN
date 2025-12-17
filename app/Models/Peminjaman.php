<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $guarded = ['id'];

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
}
