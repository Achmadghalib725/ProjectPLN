<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $fillable = ['nama'];

    /**
     * Relasi ke Items
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'satuan_id');
    }

    /**
     * Get count of items using this unit.
     */
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }
}
