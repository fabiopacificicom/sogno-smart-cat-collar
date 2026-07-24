<?php

namespace App\Providers\Sensors;

use App\Models\ProviderSetting;

/**
 * Receives sensor data POSTed directly from the collar via a tunnel.
 *
 * The desktop app exposes POST /api/sensor-data. The collar sends data there.
 * This provider is "pull"-based from the settings perspective: it reports
 * whether the API key is configured, but the actual data arrives via the
 * HTTP endpoint (handled by SensorDataController).
 */
class DirectApiProvider implements SensorDataProvider
{
    public function getName(): string
    {
        return '🔌 Direct API';
    }

    public function getKey(): string
    {
        return 'direct_api';
    }

    public function isConfigured(): bool
    {
        // The direct API is always "configured" — it's the desktop app itself.
        // The API key is optional.
        return true;
    }

    public function fetchData(): ?SensorData
    {
        // Data arrives via POST /api/sensor-data, not via polling.
        // This method exists to satisfy the interface; it returns null because
        // there is nothing to "pull" — the collar pushes to us.
        return null;
    }

    public function getSettingsFields(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => 'API Key (optional — secures the endpoint)',
                'type' => 'password',
                'placeholder' => 'Leave empty to disable auth',
            ],
            [
                'key' => 'tunnel_url',
                'label' => 'Tunnel URL (shown to the collar)',
                'type' => 'url',
                'placeholder' => 'https://your-tunnel.example.com',
            ],
        ];
    }

    /**
     * The configured API key, if any.
     */
    public function getApiKey(): ?string
    {
        return ProviderSetting::get('direct_api', 'api_key');
    }
}
