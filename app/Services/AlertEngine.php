<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Cat;
use App\Models\SensorReading;
use App\Models\Threshold;

/**
 * Evaluates sensor readings against configured thresholds and raises alerts.
 */
class AlertEngine
{
    /**
     * Evaluate a reading for a cat and create alerts if thresholds are breached.
     *
     * @return Alert[] The alerts created (empty if none).
     */
    public function evaluate(Cat $cat, SensorReading $reading): array
    {
        $alerts = [];

        // --- Temperature ---
        $tempThresholds = Threshold::forCat($cat->id, 'temperature');
        if ($tempThresholds) {
            if ($reading->temperature >= $tempThresholds->critical_value) {
                $alerts[] = $this->createAlert(
                    $cat, $reading,
                    type: 'critical',
                    vital: 'temperature',
                    value: $reading->temperature.'°C',
                    threshold: $tempThresholds->critical_value,
                    message: "Temperature {$reading->temperature}°C exceeds critical threshold ({$tempThresholds->critical_value}°C)",
                );
            } elseif ($reading->temperature >= $tempThresholds->warning_value) {
                $alerts[] = $this->createAlert(
                    $cat, $reading,
                    type: 'warning',
                    vital: 'temperature',
                    value: $reading->temperature.'°C',
                    threshold: $tempThresholds->warning_value,
                    message: "Temperature {$reading->temperature}°C exceeds warning threshold ({$tempThresholds->warning_value}°C)",
                );
            }
        }

        // --- BPM ---
        $bpmThresholds = Threshold::forCat($cat->id, 'bpm');
        if ($bpmThresholds) {
            if ($reading->bpm >= $bpmThresholds->critical_value) {
                $alerts[] = $this->createAlert(
                    $cat, $reading,
                    type: 'critical',
                    vital: 'bpm',
                    value: (string) $reading->bpm,
                    threshold: $bpmThresholds->critical_value,
                    message: "BPM {$reading->bpm} exceeds critical threshold ({$bpmThresholds->critical_value})",
                );
            } elseif ($reading->bpm >= $bpmThresholds->warning_value) {
                $alerts[] = $this->createAlert(
                    $cat, $reading,
                    type: 'warning',
                    vital: 'bpm',
                    value: (string) $reading->bpm,
                    threshold: $bpmThresholds->warning_value,
                    message: "BPM {$reading->bpm} exceeds warning threshold ({$bpmThresholds->warning_value})",
                );
            }
        }

        return $alerts;
    }

    protected function createAlert(
        Cat $cat,
        SensorReading $reading,
        string $type,
        string $vital,
        string $value,
        float $threshold,
        string $message,
    ): Alert {
        return Alert::create([
            'cat_id' => $cat->id,
            'type' => $type,
            'vital' => $vital,
            'value' => $value,
            'threshold' => $threshold,
            'message' => $message,
        ]);
    }
}
