<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['nama'];

    /**
     * Get count of items using this category.
     */
    public function getItemsCountAttribute()
    {
        return Item::whereRaw('LOWER(kategori) = ?', [strtolower($this->nama)])->count();
    }
}
