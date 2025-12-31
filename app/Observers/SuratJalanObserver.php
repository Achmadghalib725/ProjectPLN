<?php

namespace App\Observers;

use App\Events\SuratJalanStatusUpdated;
use App\Models\SuratJalan;

class SuratJalanObserver
{
    public function created(SuratJalan $suratJalan): void
    {
        event(new SuratJalanStatusUpdated($suratJalan, 'created'));
    }

    public function updated(SuratJalan $suratJalan): void
    {
        if (!$suratJalan->wasChanged('status')) {
            return;
        }

        event(new SuratJalanStatusUpdated($suratJalan, 'status_updated'));
    }
}
