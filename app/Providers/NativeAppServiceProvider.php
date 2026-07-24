<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Opens the main dashboard window and registers the system tray.
     */
    public function boot(): void
    {
        // Ensure the database exists and is migrated before opening the window.
        // In production, NativePHP points the DB to the user's app data dir but
        // does not create or migrate it automatically — so we do it here.
        $this->ensureDatabaseReady();

        Window::open()
            ->title('Smart Cat Collar')
            ->width(1024)
            ->height(720)
            ->minWidth(800)
            ->minHeight(600)
            ->rememberState();

        MenuBar::create()
            ->icon(public_path('icon.png'))
            ->label('Smart Cat Collar')
            ->tooltip('Smart Cat Collar — monitoring your cats');
    }

    /**
     * Create the SQLite database file and run migrations if needed.
     * Seeding is NOT done here — the user chooses that in the setup wizard.
     */
    protected function ensureDatabaseReady(): void
    {
        try {
            $dbPath = config('database.connections.nativephp.database')
                ?? config('database.connections.sqlite.database')
                ?? database_path('database.sqlite');

            // Create the database file if it doesn't exist
            if (! file_exists($dbPath)) {
                File::ensureDirectoryExists(dirname($dbPath));
                File::put($dbPath, '');
            }

            // Check if the database has any tables — if not, run migrations only
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            if (empty($tables)) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            // Log but don't crash — the app should still try to open
            report($e);
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '256M',
        ];
    }
}
