<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalanItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false; // Tabel ini tidak punya created_at/updated_at

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}