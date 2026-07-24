# Feature: Auto-Updater

## Objective
Integrated auto-updater that checks for new versions on launch, downloads in the background, and prompts the user to restart — no manual installer downloads needed.

## Dependencies
- Feature: Desktop Shell (needs NativePHP running for electron-updater integration)

## Stack
- electron-updater (bundled via NativePHP)
- Laravel Livewire (update UI component)
- GitHub Releases (update source)

## Expected output
- Auto-update check on app startup (silent, no UI unless update available)
- Livewire component `UpdateBanner` — shows when update is available:
  - Current version → New version
  - Changelog summary (from GitHub Release notes)
  - Download progress bar
  - "Restart to update" button (calls `quitAndInstall()`)
- Settings option: "Automatically download updates" (default: enabled)
- Settings option: "Check for updates on startup" (default: enabled)
- Manual "Check for updates" button in Settings → General
- Works with GitHub Releases as update source (NativePHP default)
- `php artisan test` green

## Status
[ ] Not started
