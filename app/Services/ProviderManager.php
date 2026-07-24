<?php

namespace App\Services;

use App\Models\ProviderSetting;
use App\Providers\Sensors\DirectApiProvider;
use App\Providers\Sensors\MockDataProvider;
use App\Providers\Sensors\SensorData;
use App\Providers\Sensors\SensorDataProvider;
use App\Providers\Sensors\TelegramProvider;
use InvalidArgumentException;

/**
 * Resolves the active provider and manages the provider registry.
 */
class ProviderManager
{
    /** @var array<string, SensorDataProvider> */
    protected array $providers = [];

    public function __construct()
    {
        $this->register(new MockDataProvider());
        $this->register(new DirectApiProvider());
        $this->register(new TelegramProvider());
    }

    public function register(SensorDataProvider $provider): void
    {
        $this->providers[$provider->getKey()] = $provider;
    }

    /** @return array<string, SensorDataProvider> */
    public function all(): array
    {
        return $this->providers;
    }

    public function get(string $key): SensorDataProvider
    {
        return $this->providers[$key] ?? throw new InvalidArgumentException("Unknown provider: {$key}");
    }

    /**
     * The currently active provider (defaults to mock).
     */
    public function active(): SensorDataProvider
    {
        $key = ProviderSetting::activeProvider() ?? 'mock';

        return $this->providers[$key] ?? $this->providers['mock'];
    }

    /**
     * Switch the active provider.
     */
    public function activate(string $key): void
    {
        if (! isset($this->providers[$key])) {
            throw new InvalidArgumentException("Unknown provider: {$key}");
        }

        ProviderSetting::activate($key);
    }

    /**
     * Fetch data from the active provider.
     */
    public function fetch(): ?SensorData
    {
        return $this->active()->fetchData();
    }
}
