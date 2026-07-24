# Feature: Mobile App

## Objective
NativePHP Mobile v3 build of the same Laravel codebase — responsive Livewire views on mobile, push notifications for critical alerts. No camera, no biometrics — data display only.

## Dependencies
- Feature: Data Layer (same Eloquent models)
- Feature: Dashboard (same Livewire views — responsive)
- Feature: Alert Engine (needs alerts for push notifications)

## Stack
- NativePHP Mobile v3
- Laravel (same codebase as desktop)
- Push notifications via NativePHP Mobile API

## Expected output
- NativePHP Mobile scaffold (`composer require nativephp/mobile`)
- Same Dashboard and Settings views render on mobile (responsive Tailwind)
- Push notification registration for `critical` and `emergency` alerts
- Mobile-optimized navigation (bottom tab bar or hamburger menu)
- Offline access: SQLite on-device, data available without network
- Build: `.apk` for Android, `.ipa` for iOS (future — requires Mac + Apple Developer account)
- For Session B demo: focus on desktop. Mobile is post-session work.

## Status
[ ] Not started
