<?php

namespace App\Providers;

use App\Models\SoketiApp;
use App\Observers\SoketiAppObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        SoketiApp::observe(SoketiAppObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
