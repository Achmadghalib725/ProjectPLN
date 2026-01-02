<?php

namespace App\Observers;

use App\Events\SuratJalanStatusUpdated;
use App\Models\SuratJalan;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

class SuratJalanObserver
{
    public function created(SuratJalan $suratJalan): void
    {
        try {
            event(new SuratJalanStatusUpdated($suratJalan, 'created'));
        } catch (BroadcastException $e) {
            Log::warning('Broadcasting failed for SuratJalan created: ' . $e->getMessage());
        }
    }

    public function updated(SuratJalan $suratJalan): void
    {
        if (!$suratJalan->wasChanged('status')) {
            return;
        }

        try {
            event(new SuratJalanStatusUpdated($suratJalan, 'status_updated'));
        } catch (BroadcastException $e) {
            Log::warning('Broadcasting failed for SuratJalan status_updated: ' . $e->getMessage());
        }
    }
}
