# Feature: Dashboard

## Objective
Main UI of the app — displays cat health cards with real-time sensor data, charts for historical trends, and an alert log. The same Livewire views render on desktop and mobile (responsive).

## Dependencies
- Feature: Data Layer (needs Cat, SensorReading, Alert models)
- Feature: Communication Providers (needs data flowing in to display)

## Stack
- Laravel Livewire components
- Tailwind CSS (responsive cards, grid layout)
- Chart.js or ApexCharts (via CDN — historical trend charts)

## Expected output
- `Dashboard` Livewire component — main view with:
  - **Cat cards** — one card per cat showing: name, photo, current temp/bpm/activity/status emoji (✅/⚠️/🚨)
  - **Detail view** — click a cat card → full sensor history with charts (temp over 24h, bpm over 24h, activity pattern)
  - **Alert log** — recent alerts list with severity badges, click to acknowledge
  - **Last updated** — timestamp of most recent sensor reading
- Polling: Livewire polls for new data every 30s when window is visible
- Empty state: friendly message when no cats configured yet
- Responsive grid: 2 columns on desktop, 1 column on mobile

## Status
[ ] Not started
