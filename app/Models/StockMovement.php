<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}