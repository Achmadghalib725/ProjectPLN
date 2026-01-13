<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    /**
     * Scope untuk filter stok berdasarkan tipe gudang
     */
    public function scopeByTipeGudang($query, $tipe)
    {
        if ($tipe && in_array($tipe, ['mekanik', 'listrik'])) {
            return $query->where('tipe_gudang', $tipe);
        }
        return $query;
    }

    /**
     * Scope untuk filter stok barang mekanik
     */
    public function scopeMekanik($query)
    {
        return $query->where('tipe_gudang', 'mekanik');
    }

    /**
     * Scope untuk filter stok barang listrik
     */
    public function scopeListrik($query)
    {
        return $query->where('tipe_gudang', 'listrik');
    }

    /**
     * Get tipe gudang label
     */
    public function getTipeGudangLabelAttribute()
    {
        return $this->tipe_gudang === 'mekanik' ? 'Mekanik' : 'Listrik';
    }
}