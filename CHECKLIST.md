# Smart Cat Collar — Project Checklist

> Last updated: 2026-07-22

## Phase 1 — Foundation (Session B live demo)

- [x] Data Layer — Eloquent models + SQLite migrations (Cat, SensorReading, Alert, Threshold, ProviderSetting, AppSetting)
- [x] Communication Providers — SensorDataProvider interface + DirectApiProvider + TelegramProvider + MockDataProvider
- [x] Desktop Shell — NativePHP Desktop scaffold, window config (1024×720), system tray
- [x] Initial Setup Screen — first-boot wizard (add cat, choose provider, set thresholds), shows once
- [x] Dashboard — Livewire cat health cards, sensor data display, alert log
- [x] Settings Page — Livewire form: providers, thresholds, cat profiles, general settings (5 tabs)
- [x] Desktop Artifact — `Smart Cat Collar-1.0.0-setup.exe` built (102.9 MB installer + portable exe)

## Phase 2 — Intelligence & Notifications

- [x] Alert Engine — threshold evaluation on every sensor reading (AlertEngine + SensorIngestService)
- [ ] Desktop notifications — native system notifications for critical alerts

## Phase 3 — Mobile Companion

- [ ] Notification foundation — alert-created event + listener (shared with Phase 2 desktop notifications)
- [ ] Mobile scaffold — `nativephp/mobile` in same repo, distinct app ID, `native:mobile` scripts
- [ ] Responsive verification — all views at 360–420px widths, touch targets ≥44px
- [ ] Mobile sync API — `devices` table, single-use pairing codes (multi-device), LAN-only read-only delta sync (see `.specs/plans/feature-mobile-sync.md`)
- [ ] Local push notifications for `critical` alerts on mobile
- [ ] Android build — `.apk`; iOS deferred (Mac + Apple Developer account)

## Phase 4 — Hardware Integration

- [ ] ESP32 firmware sends data to desktop API (separate repo)
- [ ] Switch from MockDataProvider to DirectApiProvider
- [ ] Validate sensor readings against real cat data
- [ ] Calibrate thresholds per cat

## Phase 5 — Distribution

- [ ] GitHub Actions CI — build on tag push (Win + Linux + Mac)
- [ ] Code signing (Windows OV cert, Apple Developer account)
- [ ] Store submission (Microsoft Store, Google Play)
