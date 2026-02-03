<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GroundingTestItem extends Model
{
    protected $guarded = ['id'];

    public function groundingTest(): BelongsTo
    {
        return $this->belongsTo(GroundingTest::class);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path ? Storage::url($this->attachment_path) : null;
    }
}
