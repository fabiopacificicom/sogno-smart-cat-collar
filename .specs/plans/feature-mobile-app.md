# Feature: Mobile App

## Objective
NativePHP Mobile v3 build of the same Laravel codebase — responsive Livewire views on mobile, push notifications for critical alerts. No camera, no biometrics — data display only.

## Dependencies
- Feature: Data Layer ✅ (same Eloquent models — Cat, SensorReading, Alert, Threshold, ProviderSetting, AppSetting)
- Feature: Dashboard ✅ (Livewire views exist — mostly responsive, mobile polish needed)
- Feature: Alert Engine ✅ (threshold evaluation creates Alert records)
- **NEW blocker:** no notification/event layer exists — alerts are only DB records. A Laravel event + listener is required before mobile push (and desktop native notifications) can fire. See Implementation steps 1-2.
- Mobile sync channel (see `.specs/plans/feature-mobile-sync.md`)

## Stack
- NativePHP Mobile v3 (`nativephp/mobile` — separate Composer package from `nativephp/electron`)
- Laravel 12 (same codebase as desktop — do NOT upgrade; PHP 8.3 via Herd)
- Livewire 3 + Blade + Tailwind CSS v4 (same views, responsive)
- SQLite on-device (offline-first)
- Push notifications via NativePHP Mobile API
- Android SDK + Java 17 (build prerequisites); Xcode + Apple Developer account for iOS (future)

## Current state (audit 2026-08-03)
- ✅ Routes: `/`, `/setup`, `/dashboard`, `/cats/{cat}`, `/settings` — all Livewire, no platform conditionals needed
- ✅ Layout (`resources/views/layouts/app.blade.php`): viewport meta present, mobile-first top bar, `max-w-5xl` content container
- ✅ Dashboard: mobile-first `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` cat cards
- ✅ Settings: 5 tabs in horizontally scrollable nav (`overflow-x-auto`) — usable on small screens
- ⚠️ CatDetail / SetupWizard views not yet verified at 360-420px widths
- ⚠️ No bottom tab bar / hamburger menu — top bar only (acceptable for v1, improve in polish pass)
- ❌ `nativephp/mobile` not installed; no `native:mobile` composer/npm scripts
- ❌ No push notification registration or alert→notification pipeline
- ❌ Alert model has only `critical`/`warning` severities — no `emergency` (align plan to existing severities, or add `emergency` deliberately)
- ❌ No mobile sync API (mobile cannot see desktop's collar data yet — mock/local data only)

## Implementation steps
1. **Alert event + listener (shared foundation):** fire a Laravel event when `AlertEngine` creates a `critical` alert; add a listener that dispatches to notification channels. This unblocks BOTH desktop native notifications (Phase 2) and mobile push (Phase 3). Do this first — it is desktop-side work.
2. **Mobile scaffold:** `composer require nativephp/mobile`, then `php artisan native:mobile:install` (or per NativePHP Mobile v3 docs). Add `native:mobile` / `native:mobile:run` composer scripts. Use a distinct app ID (e.g. `com.smartcatscollar.app.mobile`) from the desktop build.
3. **Responsive verification pass:** audit Dashboard, CatDetail, Settings, SetupWizard at 360×740 and 414×896; fix overflow, touch targets (min 44px), and font scaling. No layout rewrite expected.
4. **Mobile navigation (optional v1):** keep top bar; add bottom tab bar only if audit shows reachability issues.
5. **Push notifications:** register for push on first launch; map `critical` alerts to push via the listener from step 1. `warning` alerts stay in-app only. No `emergency` severity unless added to the Alert model + thresholds deliberately.
6. **Offline-first:** NativePHP Mobile boots Laravel on-device with its own SQLite DB. Data exists locally without network. Sync of real collar data is covered by `feature-mobile-sync.md`.
7. **Android build:** `php artisan native:mobile:build android` (or per docs) → `.apk`/`.aab`. Requires Android SDK, Java 17, and a signing keystore.
8. **iOS build (future):** requires macOS + Xcode + Apple Developer account → `.ipa`. Out of scope for the first milestone.

## Expected output
- NativePHP Mobile scaffold in the same repo (`composer require nativephp/mobile`)
- Alert-created event + notification listener (shared with desktop notifications)
- Same Dashboard and Settings views verified responsive on 360-420px screens
- Push notification registration + delivery for `critical` alerts
- SQLite on-device — app opens and shows local data without network
- `.apk` (debug and/or release-signed) for Android
- `.ipa` for iOS deferred (requires Mac + Apple Developer account)
- Distinct mobile app ID in `config/nativephp.php` / env

## Open questions
- App ID strategy: single `NATIVEPHP_APP_ID` today — decide per-platform env values (proposal: `com.smartcatscollar.desktop` / `com.smartcatscollar.mobile`).
- iOS timeline: blocked on Apple Developer account + Mac access.

## Decisions (confirmed with user 2026-08-03)
- ✅ First mobile milestone runs its own local SQLite (MockDataProvider for demos); pairing + LAN sync lands with `feature-mobile-sync.md`
- ✅ LAN-only sync for v1; tunnel-based remote sync goes on the roadmap
- ✅ New `devices` table — multiple family members can each pair a phone
- ✅ Single-use pairing codes

## Status
[ ] Not started
