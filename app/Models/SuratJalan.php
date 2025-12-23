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
}
