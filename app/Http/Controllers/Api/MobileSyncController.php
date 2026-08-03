<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use App\Models\Cat;
use App\Models\SensorReading;
use App\Models\Threshold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only mobile sync API. The phone pairs once (see PairingController),
 * then pulls delta syncs of the desktop hub's data into its own on-device
 * SQLite. All endpoints require a valid device token.
 *
 * Deltas use `updated_at`/`created_at` cursors: pass `?since=<ISO8601>` to
 * receive only records changed since that timestamp. Omit `since` for a full
 * sync. Read-only — writes stay on the desktop (v1).
 */
class MobileSyncController extends MobileApiController
{
    public function cats(Request $request): JsonResponse
    {
        $device = $this->authenticateDevice($request);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        $cats = Cat::query()
            ->when($request->query('since'), fn ($q, $since) => $q->where('updated_at', '>', $since))
            ->orderBy('name')
            ->get(['id', 'name', 'breed', 'birth_year', 'status', 'updated_at']);

        return response()->json(['data' => $cats, 'synced_at' => now()->toIso8601String()]);
    }

    public function readings(Request $request): JsonResponse
    {
        $device = $this->authenticateDevice($request);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        $readings = SensorReading::query()
            ->when($request->query('since'), fn ($q, $since) => $q->where('created_at', '>', $since))
            ->orderByDesc('read_at')
            ->limit(500)
            ->get(['id', 'cat_id', 'temperature', 'bpm', 'activity', 'source', 'read_at', 'created_at']);

        return response()->json(['data' => $readings, 'synced_at' => now()->toIso8601String()]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $device = $this->authenticateDevice($request);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        $alerts = Alert::query()
            ->when($request->query('since'), fn ($q, $since) => $q->where('created_at', '>', $since))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'cat_id', 'type', 'vital', 'value', 'threshold', 'message', 'acknowledged_at', 'created_at']);

        return response()->json(['data' => $alerts, 'synced_at' => now()->toIso8601String()]);
    }

    public function thresholds(Request $request): JsonResponse
    {
        $device = $this->authenticateDevice($request);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        $thresholds = Threshold::query()
            ->when($request->query('since'), fn ($q, $since) => $q->where('updated_at', '>', $since))
            ->get(['id', 'cat_id', 'vital', 'warning_value', 'critical_value', 'updated_at']);

        return response()->json(['data' => $thresholds, 'synced_at' => now()->toIso8601String()]);
    }
}
