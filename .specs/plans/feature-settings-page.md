# Feature: Settings Page

## Objective
Livewire settings page where the user configures communication providers, alert thresholds, cat profiles, API tunnel URL, and polling intervals. Persists all config via Eloquent (ProviderSetting model).

## Dependencies
- Feature: Data Layer (needs ProviderSetting, Threshold, Cat models)

## Stack
- Laravel Livewire component
- Tailwind CSS (responsive form layout)
- Eloquent for persistence

## Expected output
- Livewire component `SettingsPage` with tabs/sections:
  - **Providers** — select active provider (Direct API / Telegram / Mock), configure provider-specific fields (Telegram bot token, tunnel URL, polling interval)
  - **Cats** — CRUD for cat profiles (name, photo, breed, notes)
  - **Thresholds** — per-cat alert thresholds (temperature warn/critical, bpm warn/critical, activity low threshold)
  - **General** — app preferences (data retention days, auto-start on boot, notification sound)
- Form validation (required fields, numeric ranges for thresholds)
- Settings persist to SQLite via Eloquent
- Responsive — works on desktop (800×600) and mobile

## Status
[ ] Not started
