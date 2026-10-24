<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Zona waktu aplikasi mengikuti WITA (Asia/Makassar) agar jendela apel presensi akurat.
        config(['app.timezone' => env('APP_TIMEZONE', 'Asia/Makassar')]);
        date_default_timezone_set(config('app.timezone'));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
