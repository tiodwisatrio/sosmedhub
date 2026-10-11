<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Bahasa bawaan produk untuk konteks tanpa permintaan web (job, command, tes). Permintaan web
        // memilihnya lagi lewat middleware SetLocale sesuai cookie pengguna.
        $this->app->setLocale(config('sosmedhub.default_locale', 'id'));

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('developer')) {
                return true;
            }
        });
    }
}
