<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to the setup wizard on first boot, until setup is completed.
 * Also ensures the database exists and is migrated as a safety net.
 */
class EnsureSetupCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for API routes and the setup route itself
        if ($request->is('api/*') || $request->is('setup') || $request->is('livewire/*')) {
            return $next($request);
        }

        // Safety net: ensure the database file exists and has tables.
        // This catches the case where NativeAppServiceProvider::boot() hasn't
        // run yet or failed silently.
        $this->ensureDatabaseExists();

        try {
            if (! AppSetting::setupCompleted()) {
                return redirect()->route('setup');
            }
        } catch (\Throwable) {
            // Database not ready — redirect to setup rather than crashing
            return redirect()->route('setup');
        }

        return $next($request);
    }

    /**
     * Create the SQLite database file and run migrations if needed.
     */
    protected function ensureDatabaseExists(): void
    {
        try {
            $dbPath = config('database.connections.nativephp.database')
                ?? config('database.connections.sqlite.database')
                ?? database_path('database.sqlite');

            if (! file_exists($dbPath)) {
                File::ensureDirectoryExists(dirname($dbPath));
                File::put($dbPath, '');
            }

            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            if (empty($tables)) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable) {
            // If we can't set up the DB here, the setup wizard will handle it
        }
    }
}
