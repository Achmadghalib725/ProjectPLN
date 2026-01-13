<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $fillable = ['nama'];

    /**
     * Get count of items using this unit.
     */
    public function getItemsCountAttribute()
    {
        return Item::whereRaw('LOWER(satuan) = ?', [strtolower($this->nama)])->count();
    }
}
