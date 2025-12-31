<?php

namespace App\Providers;

use App\Models\SuratJalan;
use App\Observers\SuratJalanObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        SuratJalan::observe(SuratJalanObserver::class);
    }
}
