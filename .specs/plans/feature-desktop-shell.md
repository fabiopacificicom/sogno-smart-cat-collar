# Feature: Desktop App Shell

## Objective
NativePHP Desktop application shell — window lifecycle, system tray, native menus, auto-start, and the Electron build configuration.

## Dependencies
- Feature: Data Layer (needs models)
- Feature: Dashboard (needs views to render)

## Stack
- NativePHP Desktop v2
- Laravel (config, providers, middleware)
- electron-builder (via NativePHP build pipeline)

## Expected output
- NativePHP Desktop scaffold (`composer require nativephp/electron`)
- `NativeApp` provider registered
- Window config: 800×600, title "Smart Cat Collar", resizable
- System tray: cat status icon (✅ healthy / ⚠️ warning / 🚨 alert), context menu (Dashboard, Settings, Quit)
- Window close = hide to tray (not terminate)
- Auto-start option (configurable in Settings)
- `nativex` config for build targets (Win .exe, Linux .AppImage, Mac .dmg)
- `php artisan native:build` produces working installer
- GitHub Actions workflow: build on tag push → release with installers

## Status
[ ] Not started
