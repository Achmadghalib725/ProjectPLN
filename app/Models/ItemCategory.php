<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['nama'];

    /**
     * Relasi ke Items
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'kategori_id');
    }

    /**
     * Get count of items using this category.
     */
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }
}
