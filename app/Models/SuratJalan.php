<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
}