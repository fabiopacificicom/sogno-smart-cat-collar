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

## Design decisions (confirmed with user 2026-08-03)
1. **Topology:** LAN-only for v1. Mobile pulls from the desktop over the local network (mDNS/manual host entry). Tunnel-based remote sync is a roadmap item — the sync API design must not preclude it (token auth works the same over a tunnel; only reachability changes).
2. **Auth:** per-device pairing. Desktop Settings shows a **single-use** pairing code (short TTL, e.g. 10 minutes) + optional QR; mobile enters it once and receives a long-lived API token stored on-device. Revoking a device deletes its token.
3. **Devices table:** new `devices` table (NOT `provider_settings`) — the user may pair **multiple mobile devices** (e.g. each family member monitors the cats). Each device gets its own token, name, and revocation control.
4. **Direction:** v1 is read-only sync (desktop → mobile). Mobile does not write sensor data. Settings/threshold changes remain desktop-only in v1.
5. **Mechanism:** polling for v1 — mobile fetches deltas on launch + pull-to-refresh + timed polling (every N minutes while the app is open). WebSocket push is a roadmap item (ADR).
6. **First milestone without sync:** acceptable — mobile runs its own local SQLite (MockDataProvider for demos) until this feature lands.

## Proposed API surface (v1, read-only)
| Endpoint | Purpose |
|---|---|
| `POST /api/mobile/pair` | Exchange one-time pairing code for a device token |
| `GET /api/mobile/cats` | Cat profiles |
| `GET /api/mobile/readings?since=...` | Sensor readings delta since timestamp |
| `GET /api/mobile/alerts?since=...` | Alerts delta (mobile uses this to fire local push notifications) |
| `GET /api/mobile/thresholds` | Current thresholds (display only in v1) |

All except `/pair` require the device token (Authorization header).

### Devices table (migration sketch)
| Column | Notes |
|---|---|
| `id` | PK |
| `name` | Device label, e.g. "Fabio's Pixel" — editable in Settings |
| `token_hash` | Hashed API token (never store plaintext) |
| `paired_at` | Timestamp of successful pairing |
| `last_seen_at` | Updated on each authenticated sync request |
| `revoked_at` | Nullable — soft revocation, keeps audit trail |

Multiple rows supported — one per family member's phone.

## Implementation steps
1. Add `devices` migration + `Device` model (columns above).
2. Add pairing UI to desktop Settings (new "Mobile Devices" tab): generate single-use pairing code (10-min TTL), list paired devices, rename, revoke.
3. Add API controllers + token middleware for the endpoints above. `POST /api/mobile/pair` validates the code (single-use, TTL), creates the Device row, returns the plaintext token once.
4. Mobile side: pairing screen (enter code), secure token storage, sync service that pulls deltas into the mobile-local SQLite via the existing models (upsert by `id`, track `last_synced_at` per device).
5. Alert fan-out: the alert listener (from `feature-mobile-app.md` step 1) records alerts; mobile picks them up via `/alerts` delta and raises local push notifications for `critical`.
6. Conflict handling: none needed in v1 (read-only). Document that writes are desktop-only.

## Expected output
- Pairing flow: desktop shows single-use code/QR → mobile pairs → token stored on-device
- Multiple devices can pair; each appears in desktop Settings and can be revoked individually
- Mobile app displays the same cats, readings, and alerts as the desktop (delta-synced)
- Works on LAN without internet
- Mobile keeps functioning offline with last synced data

## Roadmap (post-v1)
- **Tunnel-based remote sync** — reuse the collar's tunnel pattern (ngrok/Cloudflare Tunnel) so sync works away from home. Token auth is unchanged; only the base URL becomes the tunnel URL. Add a "Remote access" toggle + tunnel URL field in desktop Settings.
- **WebSocket provider** — real-time push instead of polling (already in ADR roadmap).
- **Mobile write-back** — edit thresholds/settings from mobile (requires conflict handling).

## Status
[ ] Not started
</content>
