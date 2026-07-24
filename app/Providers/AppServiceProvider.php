<?php

namespace App\Providers;

use App\Listeners\RunFirstBootMigrations;
use App\Services\AlertEngine;
use App\Services\ProviderManager;
use App\Services\SensorIngestService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Native\Laravel\Events\App\ApplicationBooted;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AlertEngine::class);
        $this->app->singleton(ProviderManager::class);
        $this->app->singleton(SensorIngestService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // On first boot of the desktop app, run migrations + seed if the
        // database is empty. NativePHP sets the DB path to the user's app
        // data dir in production but doesn't migrate automatically.
        Event::listen(ApplicationBooted::class, RunFirstBootMigrations::class);
    }
}
