<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Eager load relationships
     */
    protected $with = ['kategori', 'satuan'];

    /**
     * Mapping kategori ke prefix kode
     */
    protected static array $kategoriPrefixMap = [
        'kabel' => 'KBL',
        'konstruksi' => 'KON',
        'meter' => 'MTR',
        'proteksi' => 'PRO',
        'trafo' => 'TRA',
    ];

    /**
     * Boot method untuk auto-generate kode saat creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->kode) && $item->kategori_id) {
                $kategori = ItemCategory::find($item->kategori_id);
                if ($kategori) {
                    $item->kode = self::generateKode($kategori->nama);
                }
            }
        });
    }

    /**
     * Relasi ke ItemCategory
     */
    public function kategori()
    {
        return $this->belongsTo(ItemCategory::class, 'kategori_id');
    }

    /**
     * Relasi ke ItemUnit
     */
    public function satuan()
    {
        return $this->belongsTo(ItemUnit::class, 'satuan_id');
    }

    /**
     * Accessor untuk nama kategori (backward compatibility)
     */
    public function getKategoriNamaAttribute(): ?string
    {
        return $this->kategori?->nama;
    }

    /**
     * Accessor untuk nama satuan (backward compatibility)
     */
    public function getSatuanNamaAttribute(): ?string
    {
        return $this->satuan?->nama;
    }

    /**
     * Generate kode unik berdasarkan kategori
     */
    public static function generateKode(string $kategori): string
    {
        $kategoriLower = strtolower($kategori);

        // Ambil prefix dari mapping, atau generate dari 3 huruf pertama kategori
        $prefix = self::$kategoriPrefixMap[$kategoriLower]
            ?? strtoupper(substr($kategoriLower, 0, 3));

        // Cari nomor urut terakhir untuk prefix ini
        $driver = DB::connection()->getDriverName();
        $castType = $driver === 'mysql' ? 'UNSIGNED' : 'INTEGER';
        $lastItem = self::where('kode', 'LIKE', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTRING(kode, LENGTH(?) + 2) AS {$castType}) DESC", [$prefix])
            ->first();

        if ($lastItem) {
            // Extract nomor dari kode terakhir (misal KBL-001 -> 1)
            $lastNumber = (int) substr($lastItem->kode, strlen($prefix) + 1);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format: PREFIX-001, PREFIX-002, dst
        return $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get prefix untuk kategori tertentu
     */
    public static function getPrefixForKategori(string $kategori): string
    {
        $kategoriLower = strtolower($kategori);
        return self::$kategoriPrefixMap[$kategoriLower]
            ?? strtoupper(substr($kategoriLower, 0, 3));
    }

    // Untuk melihat stok item ini di berbagai gudang
    public function stocks()
    {
        return $this->hasMany(ItemStock::class);
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeMekanik($query)
    {
        return $query->where('tipe', 'mekanik');
    }

    public function scopeListrik($query)
    {
        return $query->where('tipe', 'listrik');
    }

    public function scopeByTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Get label untuk tipe
     */
    public function getTipeLabelAttribute(): string
    {
        return $this->tipe === 'mekanik' ? 'Mekanik' : 'Listrik';
    }
}
