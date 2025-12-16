<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Untuk melihat stok item ini di berbagai gudang
    public function stocks()
    {
        return $this->hasMany(ItemStock::class);
    }
}