<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Cat;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\Threshold;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mobile-side sync client. Runs only on the phone (System::isMobile()).
 *
 * Pairing: POSTs the single-use code to the desktop hub's /api/mobile/pair,
 * receives a long-lived device token, and stores it (plus the hub base URL)
 * on-device. The token is kept in SecureStorage when the native runtime is
 * available, falling back to app_settings otherwise (e.g. testing via Jump
 * in a plain browser where the SecureStorage native bridge isn't present).
 *
 * Sync: pulls delta changes (cats, readings, alerts, thresholds) from the
 * hub into the phone's local SQLite via the shared Eloquent models, using a
 * `since` cursor per resource stored in app_settings. Read-only — the phone
 * never writes sensor data back to the hub (v1).
 */
class MobileSyncService
{
    protected const TOKEN_KEY = 'mobile_device_token';

    protected const HOST_KEY = 'mobile_hub_host';

    /**
     * Whether the app is currently running on a phone (NativePHP Mobile),
     * as opposed to the desktop Electron shell or a plain web server.
     */
    public static function isMobileRuntime(): bool
    {
        try {
            return class_exists(\Native\Mobile\Facades\System::class)
                && \Native\Mobile\Facades\System::isMobile();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Whether this device has been paired with a hub.
     */
    public static function isPaired(): bool
    {
        return filled(static::token()) && filled(static::pairedHost());
    }

    public static function pairedHost(): ?string
    {
        return static::readStore(self::HOST_KEY);
    }

    public static function token(): ?string
    {
        return static::readStore(self::TOKEN_KEY);
    }

    /**
     * Pair with a desktop hub. Returns ['ok' => bool, 'error' => ?string].
     */
    public function pair(string $host, string $code): array
    {
        $base = $this->normalizeHost($host);

        try {
            $response = Http::timeout(10)->post("{$base}/api/mobile/pair", [
                'code' => $code,
                'name' => $this->deviceName(),
            ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Could not reach the desktop at '.$base.'. Is it on the same WiFi?'];
        }

        if ($response->status() === 422) {
            return ['ok' => false, 'error' => 'Invalid or expired code. Generate a new one on the desktop.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'error' => 'Pairing failed (HTTP '.$response->status().').'];
        }

        $token = $response->json('token');

        if (! $token) {
            return ['ok' => false, 'error' => 'The desktop did not return a token.'];
        }

        // Persist host + token, then run the first full sync.
        static::writeStore(self::HOST_KEY, $base);
        static::writeStore(self::TOKEN_KEY, $token);

        $sync = $this->syncAll();

        if (! $sync['ok']) {
            // Keep the pairing but report the sync problem.
            return ['ok' => true, 'error' => null, 'warning' => $sync['error'] ?? 'Initial sync failed'];
        }

        return ['ok' => true, 'error' => null];
    }

    /**
     * Pull all delta changes from the hub into local SQLite.
     */
    public function syncAll(): array
    {
        if (! static::isPaired()) {
            return ['ok' => false, 'error' => 'Not paired'];
        }

        try {
            $this->syncResource('cats', Cat::class, ['name', 'breed', 'birth_year', 'status'], 'updated_at');
            $this->syncResource('readings', SensorReading::class, ['cat_id', 'temperature', 'bpm', 'activity', 'source', 'read_at'], 'created_at');
            $this->syncResource('alerts', Alert::class, ['cat_id', 'type', 'vital', 'value', 'threshold', 'message', 'acknowledged_at'], 'created_at');
            $this->syncResource('thresholds', Threshold::class, ['cat_id', 'vital', 'warning_value', 'critical_value'], 'updated_at');
        } catch (\Throwable $e) {
            Log::warning('Mobile sync failed', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        return ['ok' => true];
    }

    /**
     * Sync one resource: fetch deltas since the stored cursor and upsert.
     */
    protected function syncResource(string $resource, string $model, array $fields, string $cursorColumn): void
    {
        $cursorKey = "mobile_sync_since_{$resource}";
        $since = \App\Models\AppSetting::get($cursorKey);

        $params = [];
        if ($since) {
            $params['since'] = $since;
        }

        $response = $this->client()->get($this->url("/api/mobile/{$resource}"), $params);

        if (! $response->successful()) {
            throw new \RuntimeException("Sync {$resource} failed (HTTP {$response->status()})");
        }

        foreach ($response->json('data', []) as $row) {
            $values = ['id' => $row['id']];
            foreach ($fields as $field) {
                if (array_key_exists($field, $row)) {
                    $values[$field] = $row[$field];
                }
            }
            $model::updateOrCreate(['id' => $row['id']], $values);
        }

        // Advance the cursor to the server's sync timestamp.
        if ($syncedAt = $response->json('synced_at')) {
            \App\Models\AppSetting::set($cursorKey, $syncedAt);
        }
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(15)->withToken(static::token())->acceptJson();
    }

    protected function url(string $path): string
    {
        return static::pairedHost().$path;
    }

    protected function normalizeHost(string $host): string
    {
        $host = trim($host);
        if (! str_starts_with($host, 'http://') && ! str_starts_with($host, 'https://')) {
            $host = 'http://'.$host;
        }

        return rtrim($host, '/');
    }

    protected function deviceName(): string
    {
        return 'Phone'; // could use the Device facade for model name later
    }

    /**
     * Read a value from SecureStorage (native) with app_settings fallback.
     */
    protected static function readStore(string $key): ?string
    {
        if (static::secureStorageAvailable()) {
            $value = \Native\Mobile\Facades\SecureStorage::get($key);
            if ($value !== null) {
                return $value;
            }
        }

        return \App\Models\AppSetting::get($key);
    }

    protected static function writeStore(string $key, string $value): void
    {
        if (static::secureStorageAvailable()) {
            \Native\Mobile\Facades\SecureStorage::set($key, $value);
        }
        // Always mirror to app_settings so non-native contexts (Jump/browser)
        // keep working.
        \App\Models\AppSetting::set($key, $value);
    }

    protected static function secureStorageAvailable(): bool
    {
        return class_exists(\Native\Mobile\Facades\SecureStorage::class)
            && function_exists('nativephp_running_in_mobile_app');
    }
}
