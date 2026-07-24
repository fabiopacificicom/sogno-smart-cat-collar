<?php

namespace App\Providers\Sensors;

/**
 * Generates realistic fake sensor data for development.
 *
 * Used until the ESP32 collar hardware is assembled. Swap to DirectApiProvider
 * in settings once the collar is live.
 */
class MockDataProvider implements SensorDataProvider
{
    public function getName(): string
    {
        return '🧪 Mock Data';
    }

    public function getKey(): string
    {
        return 'mock';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function fetchData(): ?SensorData
    {
        // Generate values within realistic cat ranges, occasionally spiking
        // to demonstrate the warning/critical alert paths.
        $spike = mt_rand(1, 20) === 1; // 5% chance of a critical spike

        return new SensorData(
            catId: null, // the caller picks a cat
            temperature: $spike ? mt_rand(395, 405) / 10 : mt_rand(378, 392) / 10,
            bpm: $spike ? mt_rand(250, 280) : mt_rand(120, 230),
            activity: ['low', 'medium', 'high'][mt_rand(0, 2)],
            source: 'mock',
            deviceId: 'esp32s3-mock-01',
            readAt: now(),
        );
    }

    public function getSettingsFields(): array
    {
        return [
            [
                'key' => 'interval',
                'label' => 'Polling interval (seconds)',
                'type' => 'text',
                'placeholder' => '30',
            ],
        ];
    }
}
