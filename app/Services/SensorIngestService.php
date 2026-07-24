<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Cat;
use App\Models\SensorReading;
use App\Models\Threshold;
use App\Providers\Sensors\SensorData;

/**
 * Ingests sensor data, stores readings, evaluates thresholds, and raises alerts.
 */
class SensorIngestService
{
    public function __construct(
        protected AlertEngine $alertEngine,
    ) {}

    /**
     * Persist a sensor reading and trigger alert evaluation.
     */
    public function ingest(SensorData $data, ?int $catId = null): ?SensorReading
    {
        $catId = $catId ?? $data->catId;

        if (! $catId) {
            // No cat specified — pick the first one (dev convenience for mock data)
            $cat = Cat::first();
            if (! $cat) {
                return null;
            }
            $catId = $cat->id;
        }

        $cat = Cat::find($catId);
        if (! $cat) {
            return null;
        }

        $reading = SensorReading::create([
            'cat_id' => $cat->id,
            'temperature' => $data->temperature,
            'bpm' => $data->bpm,
            'activity' => $data->activity,
            'source' => $data->source,
            'read_at' => $data->readAt ?? now(),
        ]);

        // Evaluate thresholds and create alerts if needed
        $this->alertEngine->evaluate($cat, $reading);

        // Recompute the cat's status
        $cat->recomputeStatus();

        return $reading;
    }
}
