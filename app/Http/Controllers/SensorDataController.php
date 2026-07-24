<?php

namespace App\Http\Controllers;

use App\Models\ProviderSetting;
use App\Providers\Sensors\SensorData;
use App\Services\SensorIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives sensor data POSTed from the ESP32 collar via a tunnel.
 *
 * Endpoint: POST /api/sensor-data
 */
class SensorDataController extends Controller
{
    public function __construct(
        protected SensorIngestService $ingestService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'cat_id' => 'nullable|integer|exists:cats,id',
            'temperature' => 'required|numeric|min:30|max:45',
            'bpm' => 'required|integer|min:40|max:300',
            'activity' => 'required|in:low,medium,high',
            'timestamp' => 'nullable|date',
        ]);

        // Optional API key check
        $apiKey = ProviderSetting::get('direct_api', 'api_key');
        if (! empty($apiKey)) {
            $provided = $request->bearerToken() ?? $request->query('key');
            if ($provided !== $apiKey) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $data = SensorData::fromArray(array_merge($validated, ['source' => 'direct_api']));

        $reading = $this->ingestService->ingest($data, $validated['cat_id'] ?? null);

        if (! $reading) {
            return response()->json(['error' => 'Cat not found'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'cat_id' => $reading->cat_id,
            'reading_id' => $reading->id,
            'cat_status' => $reading->cat->fresh()->status,
        ], 201);
    }
}
