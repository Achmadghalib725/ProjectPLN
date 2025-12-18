<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pic;

class SuratJalan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'waktu_ttd_pembuat' => 'datetime',
    ];

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
}
