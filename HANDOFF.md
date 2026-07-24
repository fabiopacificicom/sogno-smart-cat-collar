# SESSION HANDOFF — Smart Cat Collar Companion App

**Date:** 2026-07-22
**From:** Olly (OpenClaw agent)
**To:** VSCode Agent / Next developer
**Project:** Smart Cat Collar — Desktop & Mobile Companion App

---

## Environment Setup

- **Location:** `C:\Users\fabio\Herd\smart-cats-collar-desktop`
- **Runtime:** Laravel Herd on Windows (PHP 8.3+)
- **WSL access:** Project is visible at `/mnt/c/Users/fabio/Herd/smart-cats-collar-desktop/` but **do NOT run PHP/Composer from WSL** — Herd's PHP runs natively on Windows
- **All commands** (`composer`, `php artisan`, `vendor/bin/pest`) must run from **Windows terminal** (PowerShell/CMD) or VSCode's integrated terminal
- **Initial setup:**
  ```powershell
  cd C:\Users\fabio\Herd\smart-cats-collar-desktop
  composer install
  cp .env.example .env
  php artisan key:generate
  ```
- **Database:** SQLite (`database/database.sqlite`) — Herd handles this automatically
- **Dev server:** `php artisan serve` or use Herd's auto-valet (`smart-cats-collar-desktop.test`)

---

## Quick Start

1. **Read `ADR.md`** first — all architectural decisions are there. Every line of code must respect it.
2. **Read `AGENTS.md`** — repo workflow rules (branch-per-task, test before commit, etc.)
3. **Read `CHECKLIST.md`** — current task status
4. **Browse `prototype/`** — fully functional HTML wireframe with all screens and mock data. This IS the UI spec.
5. **Read `docs/smart-collar-cat-project-specs.md`** — hardware specs for the ESP32S3 collar

## What This Project Is

A **single Laravel codebase** that powers both:
- **Desktop companion app** (NativePHP Desktop v2 / Electron) — the central data hub
- **Mobile companion app** (NativePHP Mobile v3 / Swift-Kotlin shell) — data display only

The desktop app receives sensor data from a smart cat collar (ESP32S3 Sense), stores it in SQLite, and displays cat health status. The mobile app shows the same data from the same codebase.

**10 real cats** will be monitored. This is a real product, not a toy demo.

## Architecture Summary

```
Collar ESP32S3 ──POST via tunnel──► Desktop App (NativePHP/Electron)
                                      │
                                      ├── Internal API (Laravel routes)
                                      ├── SQLite (Eloquent, offline-first)
                                      ├── Communication Providers (pluggable)
                                      │   ├── DirectApiProvider (primary)
                                      │   ├── TelegramProvider (fallback)
                                      │   └── MockDataProvider (dev)
                                      ├── Dashboard (Livewire)
                                      ├── Settings Page (Livewire)
                                      ├── Setup Wizard (first boot only)
                                      ├── Alert Engine (threshold evaluation)
                                      └── Auto-updater (electron-updater)
                                      
Mobile App (same codebase) ──reads──► SQLite
```

**Key principle:** Desktop = central data hub. Not Telegram-dependent. Communication channels are configurable via settings.

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Desktop | NativePHP Desktop v2 (Electron shell) |
| Mobile | NativePHP Mobile v3 (Swift/Kotlin shell) |
| Frontend | Blade + Livewire + Tailwind CSS |
| Database | SQLite + Eloquent ORM (offline-first) |
| CSS | Tailwind CSS |
| Testing | Pest PHP |
| Build (desktop) | `php artisan native:build` |
| Build (mobile) | NativePHP Mobile pipeline |
| Auto-update | electron-updater (bundled via NativePHP) |

## Cat Names (Real Data)

The app monitors these 7 cats (will grow to 10):
1. **Antifa** — 🟢 Healthy (38.4°C, 175 bpm, high activity)
2. **Anakin** — 🟢 Healthy (38.7°C, 190 bpm, medium activity)
3. **Mando** — 🟡 Warning (39.1°C, 225 bpm, low activity)
4. **Grogu** — 🔴 Critical (39.8°C, 260 bpm, low activity)
5. **Gaza** — 🟢 Healthy (38.2°C, 160 bpm, high activity)
6. **Jabba** — 🟢 Healthy (38.9°C, 185 bpm, medium activity)
7. **Sabbia** — 🟢 Healthy (38.6°C, 170 bpm, medium activity)

Normal cat vitals: Temp 37.8–39.2°C, BPM 120–240, Activity low/medium/high.

## Prototype (UI Spec)

The `prototype/` folder contains a fully functional HTML wireframe. **This IS the UI spec.** Port each page into Blade/Livewire components.

| Prototype File | Laravel Equivalent |
|---|---|
| `prototype/index.html` | Middleware redirect (setup or dashboard) |
| `prototype/setup.html` | `SetupWizard` Livewire component (5 steps) |
| `prototype/dashboard.html` | `Dashboard` Livewire component + `CatCard` components |
| `prototype/cat-detail.html` | `CatDetail` Livewire component |
| `prototype/settings.html` | `Settings` Livewire component (5 tabs) |
| `prototype/alert.html` | `AlertBanner` component + notification logic |

**Design tokens:**
- Primary: Orange (#F97316)
- Healthy: Teal (#14B8A6)
- Warning: Amber (#F59E0B)
- Critical: Red (#EF4444)
- Background: Gray-50 (#F9FAFB)
- Text: Gray-800 (#111827)

**Note:** Millie (designer agent) is working on polished mockups in Penpot. The prototype may evolve based on her feedback. Check with Fabio before final styling.

## Eloquent Models (Data Layer)

These models need to be created. Schema is defined by the ADR.

### Cat
- `id`, `name`, `breed` (nullable), `photo_path` (nullable), `birth_year` (nullable)
- `status` enum: healthy, warning, critical (computed from latest readings)
- HasMany: SensorReading, Alert
- HasOne: CatThreshold (per-cat overrides)

### SensorReading
- `id`, `cat_id` (FK), `temperature` (float), `bpm` (int), `activity` (enum: low/medium/high)
- `source` (enum: direct_api, telegram, mock)
- `read_at` (timestamp)
- BelongsTo: Cat

### Alert
- `id`, `cat_id` (FK), `type` (enum: warning, critical, info)
- `vital` (enum: temperature, bpm, activity)
- `value` (string), `threshold` (float, nullable)
- `message` (text), `acknowledged_at` (timestamp, nullable)
- `created_at`
- BelongsTo: Cat

### Threshold
- `id`, `cat_id` (FK, nullable — null = global default)
- `vital` (enum: temperature, bpm)
- `warning_value` (float), `critical_value` (float)
- BelongsTo: Cat (or global if cat_id null)

### ProviderSetting
- `id`, `provider` (string: direct_api, telegram, mock)
- `key` (string), `value` (text, nullable)
- `is_active` (boolean, default false)

### AppSetting
- `id`, `key` (string), `value` (text, nullable)
- Keys: `setup_completed`, `theme`, `language`, `auto_start`, `start_minimized`, `auto_download_updates`, `notification_sound`

## Communication Provider Interface

```php
interface SensorDataProvider
{
    public function getName(): string;
    public function isConfigured(): bool;
    public function fetchData(): ?SensorData;
    public function getSettingsFields(): array;
}
```

Three implementations:
1. **DirectApiProvider** — Receives POST from collar via tunnel. The desktop app exposes `POST /api/sensor-data`.
2. **TelegramProvider** — Polls Telegram Bot API for collar messages.
3. **MockDataProvider** — Generates fake sensor data for development. See `prototype/js/app.js` for the mock data structure.

## Internal API Endpoint

The desktop app exposes this route for the collar:

```
POST /api/sensor-data
Content-Type: application/json

{
  "device_id": "esp32s3-collar-01",
  "cat_id": 1,
  "temperature": 38.5,
  "bpm": 180,
  "activity": "medium",
  "timestamp": "2026-07-22T20:05:00Z"
}
```

Optionally protected by API key (configurable in settings).

## Setup Wizard Logic

1. On first launch, check `AppSetting::where('key', 'setup_completed')->first()`
2. If null or false → redirect to Setup Wizard
3. Wizard collects: first cat, provider choice, thresholds
4. On completion → set `setup_completed = true`, redirect to dashboard
5. Wizard is never shown again (all settings editable via Settings page)

## Development Order (from CHECKLIST.md)

### Phase 1 — Foundation (CURRENT)
1. **Data Layer** — Eloquent models + SQLite migrations + factories + seeders
2. **Communication Providers** — Interface + MockDataProvider (first), DirectApiProvider, TelegramProvider
3. **Desktop Shell** — `composer require nativephp/electron`, window config, system tray
4. **Setup Wizard** — 5-step Livewire component
5. **Dashboard** — Cat cards + alert log Livewire components
6. **Settings Page** — 5-tab Livewire form
7. **Auto-updater** — electron-updater integration + in-app UI

### Phase 2–5 — See CHECKLIST.md

## Testing Rules

- **Pest PHP** — no PHPUnit, use Pest syntax
- **Isolated test database** — use `:memory:` SQLite for tests
- **Every feature needs tests** before merge
- **No custom test scripts** — use the project's Pest suite only
- Tests must run with `php artisan test` or `vendor/bin/pest`

## Git Workflow

- **Branch per feature** — e.g. `feat/data-layer`, `feat/mock-provider`, `feat/dashboard`
- **Commit messages:** conventional commits — `feat:`, `fix:`, `refactor:`, `test:`, `docs:`
- **Do NOT push to main directly** — always via branch + PR
- **Run tests before every commit**

## Project Constraints

- **Single Laravel codebase** — no splitting into separate projects
- **Offline-first** — SQLite on-device, no cloud dependency
- **Desktop = data hub** — collar sends data to desktop, not the other way around
- **NOT Telegram-dependent** — Telegram is one configurable provider
- **Mobile: no camera or biometrics** — data display only
- **Mock data until hardware is ready** — ESP32 device not assembled yet
- **contextIsolation: true** — enforced by NativePHP
- **Responsive** — same Blade/Livewire views on desktop (800×600) and mobile (375px)
- **System tray** — desktop app minimizes to tray when closed

## Files in This Repo

**Location:** `C:\Users\fabio\Herd\smart-cats-collar-desktop`

```
smart-cats-collar-desktop\
├── ADR.md                          ← Architecture Decision Record (READ FIRST)
├── AGENTS.md                       ← Repo workflow rules
├── CHECKLIST.md                    ← Development checklist (keep updated)
├── HANDOFF.md                      ← THIS FILE
├── architecture.mmd                ← Mermaid diagram source
├── architecture.svg                ← Generated diagram
├── docs/
│   └── smart-collar-cat-project-specs.md  ← Hardware specs
├── prototype/                      ← HTML wireframe (UI SPEC)
│   ├── index.html
│   ├── setup.html
│   ├── dashboard.html
│   ├── cat-detail.html
│   ├── settings.html
│   ├── alert.html
│   ├── css/app.css
│   └── js/app.js
└── .specs/plans/                   ← Feature breakdowns (from tmp/, may need copying)
```

## Contact

- **Fabio (pacificDev)** — project owner, reachable via Telegram
- **Millie (designer agent)** — handles UI/UX design, Penpot mockups, Gitea sharing
- **Olly (dev agent)** — wrote this handoff, available via OpenClaw

## Known Gaps / TODOs

- [ ] No Laravel app scaffolded yet — this is a greenfield project
- [ ] Millie's design review pending — prototype colors/layout may change
- [ ] NativePHP Desktop v2 and Mobile v3 packages need version pinning
- [ ] No CI/CD pipeline yet — add GitHub Actions when ready
- [ ] ESP32 firmware is a separate project (not in this repo)
- [ ] Tunnel solution (ngrok/Cloudflare) needs testing with DirectApiProvider

---

**Start here:** `ADR.md` → `AGENTS.md` → `prototype/dashboard.html` → scaffold Laravel app → build Data Layer.
