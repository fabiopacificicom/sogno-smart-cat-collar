# Feature: Mobile Sync (Desktop ↔ Mobile data channel)

## Objective
Define and implement how the mobile companion app receives collar data, alerts, thresholds, and cat profiles from the desktop app (the central data hub). Without this, the mobile app only shows its own local/mock data.

## Dependencies
- Feature: Data Layer ✅ (shared Eloquent models)
- Feature: Mobile App (NativePHP Mobile scaffold must exist to consume the sync API)
- Desktop app running and reachable (local network or tunnel)

## Stack
- Laravel API routes in the existing codebase (`routes/api.php`)
- Sanctum-style token or pairing-code auth (decide — see Open questions)
- HTTP + JSON for v1; WebSocket provider is a roadmap item (ADR)

## Context (from ADR)
- Desktop is the central data hub: collar POSTs to the desktop's internal API; mobile reads from the desktop.
- ADR architecture diagram shows `DB --> Mobile` / `Data API ──► mobile sync` but no design existed — this plan fills that gap.
- Offline-first constraint: mobile must keep working with its last-synced local SQLite copy when the desktop is unreachable.

## Design decisions to make (before coding)
1. **Topology:** mobile pulls from desktop over LAN (mDNS/manual host entry) or via the same tunnel used by the collar (ngrok/Cloudflare Tunnel). LAN-first is simpler and offline-friendly at home; tunnel enables remote access.
2. **Auth:** per-device pairing. Proposal: desktop Settings page shows a one-time pairing code + QR; mobile enters it and receives a long-lived API token stored on-device. Token identifies the device in `provider_settings` or a new `devices` table.
3. **Direction:** v1 is read-only sync (desktop → mobile). Mobile does not write sensor data. Settings/threshold changes remain desktop-only in v1.
4. **Mechanism:** polling for v1 (e.g. mobile fetches deltas on launch + every N minutes while open). WebSocket push is a roadmap item.

## Proposed API surface (v1, read-only)
| Endpoint | Purpose |
|---|---|
| `POST /api/mobile/pair` | Exchange one-time pairing code for a device token |
| `GET /api/mobile/cats` | Cat profiles |
| `GET /api/mobile/readings?since=...` | Sensor readings delta since timestamp |
| `GET /api/mobile/alerts?since=...` | Alerts delta (mobile uses this to fire local push notifications) |
| `GET /api/mobile/thresholds` | Current thresholds (display only in v1) |

All except `/pair` require the device token (Authorization header).

## Implementation steps
1. Decide topology + auth (answer Open questions with user).
2. Add `devices` migration (id, name, token hash, paired_at, last_seen_at) — or reuse `provider_settings` if simpler.
3. Add pairing UI to desktop Settings (new "Mobile Devices" tab): generate/revoke pairing codes, list paired devices.
4. Add API controllers + token middleware for the endpoints above.
5. Mobile side: pairing screen (enter code), token storage, sync service that pulls deltas into the mobile-local SQLite via the existing models.
6. Alert fan-out: the alert listener (from `feature-mobile-app.md` step 1) records alerts; mobile picks them up via `/alerts` delta and raises local push notifications for `critical`.
7. Conflict handling: none needed in v1 (read-only). Document that writes are desktop-only.

## Expected output
- Pairing flow: desktop shows code/QR → mobile pairs → token stored
- Mobile app displays the same cats, readings, and alerts as the desktop (delta-synced)
- Works on LAN without internet; works via tunnel when remote
- Mobile keeps functioning offline with last synced data
- Device revocation from desktop Settings

## Open questions
- LAN-only v1, or tunnel-based remote access from day one? (Tunnel is already a project pattern for the collar.)
- Pairing code TTL and single-use vs. reusable?
- Do we need a `devices` table, or is a ProviderSetting entry enough for a single-user app?
- Sync cadence: on-launch + manual pull-to-refresh, or timed polling?

## Status
[ ] Not started
