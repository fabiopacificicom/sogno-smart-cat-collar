# Feature: Initial Setup Screen

## Objective
A guided setup wizard that appears only on first launch. Collects essential config (first cat, data provider, alert thresholds) before showing the dashboard. Ensures new users never see an empty, confusing screen.

## Dependencies
- Feature: Data Layer (needs Cat, ProviderSetting, Threshold models)
- Feature: Communication Providers (needs provider selection logic)

## Stack
- Laravel Livewire component (multi-step wizard)
- Tailwind CSS (responsive wizard layout)
- Eloquent for persistence + `setup_completed` flag

## Expected output
- Livewire component `SetupWizard` with 5 steps:
  1. **Welcome** — "Let's set up your Smart Cat Collar" + brief description
  2. **Add your first cat** — name (required), breed (optional), photo (optional)
  3. **Choose data source** — MockDataProvider (recommended, pre-selected), DirectApiProvider, TelegramProvider — with brief explanation of each
  4. **Set alert thresholds** — temperature warn/critical, bpm warn/critical — with sensible defaults pre-filled (temp >39.5°C, bpm >250) — "Use defaults" shortcut
  5. **Done** — summary of choices, "Start monitoring" button → loads dashboard
- `setup_completed` boolean stored in ProviderSetting (or app_settings table)
- Middleware or boot check: if `setup_completed` is false → redirect to SetupWizard instead of Dashboard
- SetupWizard is NOT accessible from navigation after completion — all settings editable via Settings page
- Responsive — works on desktop (centered card) and mobile (full-width)

## Status
[ ] Not started
