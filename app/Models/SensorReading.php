<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_id',
        'temperature',
        'bpm',
        'activity',
        'source',
        'read_at',
    ];

    protected $casts = [
        'temperature' => 'float',
        'bpm' => 'integer',
        'read_at' => 'datetime',
    ];

    /**
     * The cat this reading belongs to.
     */
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }

    /**
     * Evaluate the health status of this reading against thresholds.
     *
     * @return string healthy|warning|critical
     */
    public function evaluateStatus(?Cat $cat = null): string
    {
        $cat ??= $this->cat;

        $tempThresholds = Threshold::forCat($cat?->id, 'temperature');
        $bpmThresholds = Threshold::forCat($cat?->id, 'bpm');

        // Critical takes precedence
        if (
            ($tempThresholds && $this->temperature >= $tempThresholds->critical_value) ||
            ($bpmThresholds && $this->bpm >= $bpmThresholds->critical_value)
        ) {
            return 'critical';
        }

        if (
            ($tempThresholds && $this->temperature >= $tempThresholds->warning_value) ||
            ($bpmThresholds && $this->bpm >= $bpmThresholds->warning_value)
        ) {
            return 'warning';
        }

        return 'healthy';
    }
}
