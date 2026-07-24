<?php

namespace App\Providers\Sensors;

/**
 * Pluggable sensor data source contract.
 *
 * Implementations:
 * - DirectApiProvider  — collar POSTs to the desktop app's internal API
 * - TelegramProvider   — polls Telegram Bot API for collar messages
 * - MockDataProvider   — generates fake sensor data for development
 */
interface SensorDataProvider
{
    /**
     * Human-readable name shown in the settings UI.
     */
    public function getName(): string;

    /**
     * Machine key (e.g. "mock", "direct_api", "telegram").
     */
    public function getKey(): string;

    /**
     * Whether all required configuration is present.
     */
    public function isConfigured(): bool;

    /**
     * Fetch the latest sensor data, or null if unavailable.
     */
    public function fetchData(): ?SensorData;

    /**
     * Settings form fields for the settings page.
     * Each item: ['key' => ..., 'label' => ..., 'type' => 'text|password|url', 'placeholder' => ...]
     */
    public function getSettingsFields(): array;
}
