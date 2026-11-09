<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\UjianMurid;
use App\Observers\UjianMuridObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        UjianMurid::observe(UjianMuridObserver::class);
    }
}