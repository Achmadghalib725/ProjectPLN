<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroundingTest extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(GroundingTestItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
