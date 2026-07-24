# Feature: Communication Providers

## Objective
Pluggable `SensorDataProvider` interface with three implementations: DirectApiProvider (collar POSTs to desktop), TelegramProvider (polls Telegram Bot API), MockDataProvider (generates fake data for development).

## Dependencies
- Feature: Data Layer (needs Eloquent models to store fetched data)

## Stack
- PHP interface + implementations
- Laravel HTTP client (for Telegram API polling)
- Laravel routes (for internal API endpoint)

## Expected output
- `SensorDataProvider` interface: `getName()`, `isConfigured()`, `fetchData()`, `getSettingsFields()`
- `DirectApiProvider` — registers `POST /api/sensor-data` route, stores incoming data via Eloquent
- `TelegramProvider` — polls Telegram Bot API `getUpdates`, parses collar messages, stores as SensorReadings
- `MockDataProvider` — generates realistic fake data within feline physiological ranges (temp 38.1-39.2°C, bpm 100-240, activity 0-2g)
- `ProviderManager` service — resolves active provider from ProviderSetting, switches providers
- Unit tests for each provider (mock HTTP for Telegram, fake request for DirectApi, deterministic output for Mock)
- `php artisan test` green

## Status
[ ] Not started
