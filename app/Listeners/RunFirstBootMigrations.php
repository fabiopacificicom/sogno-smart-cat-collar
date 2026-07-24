<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Native\Laravel\Events\App\ApplicationBooted;

/**
 * Runs migrations and seeds the database on first boot of the desktop app.
 *
 * In production, NativePHP sets the SQLite database path to the user's app data
 * directory but does NOT automatically create or migrate it. This listener fills
 * that gap: on the ApplicationBooted event, if the database has no tables, we
 * run migrate + seed.
 */
class RunFirstBootMigrations
{
    public function handle(ApplicationBooted $event): void
    {
        try {
            // Check if the database has any tables yet.
            // If not, this is a first boot — run migrations and seed.
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            if (empty($tables)) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            // If the database file doesn't exist at all, create it first.
            $dbPath = config('database.connections.nativephp.database')
                ?? config('database.connections.sqlite.database')
                ?? database_path('database.sqlite');

            if (! file_exists($dbPath)) {
                File::ensureDirectoryExists(dirname($dbPath));
                File::put($dbPath, '');
            }

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
        }
    }
}
