<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SuratJalanAttachment extends Model
{
    protected $guarded = ['id'];

    public function suratJalan(): BelongsTo
    {
        return $this->belongsTo(SuratJalan::class);
    }

    /**
     * Get the full URL to the attachment
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the full path to the attachment
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->file_path);
    }
}
