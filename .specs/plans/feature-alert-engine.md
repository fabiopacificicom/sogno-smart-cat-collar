# Feature: Alert Engine

## Objective
Evaluates incoming sensor readings against configured thresholds and generates alerts. Triggers desktop native notifications and (future) mobile push notifications.

## Dependencies
- Feature: Data Layer (needs SensorReading, Alert, Threshold models)
- Feature: Communication Providers (needs data flowing in to evaluate)

## Stack
- Laravel Events + Listeners
- NativePHP Notification class (desktop alerts)

## Expected output
- `SensorDataReceived` event — fired when new SensorReading is stored
- `EvaluateAlerts` listener — checks reading against Threshold for the cat
- Alert types: fever (temp > critical), tachycardia (bpm > critical), lethargy (activity < threshold over 2h), tremor (detected + temp elevated)
- Severity levels: `warning`, `critical`, `emergency`
- Alert stored in `alerts` table with type, severity, message, sensor_reading_id
- Desktop notification via NativePHP `Notification` class for `critical` and `emergency`
- Notification click → open app to the relevant cat's detail view
- Deduplication: don't re-alert for the same condition within 30 minutes

## Status
[ ] Not started
