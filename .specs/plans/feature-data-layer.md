# Feature: Data Layer

## Objective
Eloquent models and SQLite schema for cat profiles, sensor readings, alert history, threshold config, and provider settings — the shared data foundation for both desktop and mobile.

## Dependencies
None — this is the foundation.

## Stack
- Laravel Eloquent ORM
- SQLite (offline-first, embedded)
- Migrations + seeders

## Expected output
- `Cat` model (name, photo, breed, date_of_birth, notes)
- `SensorReading` model (cat_id, temperature, heart_rate, sp02, activity_x/y/z, tremor_detected, sneeze_count, source_provider, read_at)
- `Alert` model (cat_id, type, severity, message, sensor_reading_id, acknowledged_at)
- `Threshold` model (cat_id, parameter, warn_value, critical_value)
- `ProviderSetting` model (provider_name, key, value, is_active)
- Migrations for all tables
- Factory + seeder with realistic cat data
- `php artisan migrate` and `php artisan test` green

## Status
[ ] Not started
