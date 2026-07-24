<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'breed',
        'photo_path',
        'birth_year',
        'status',
    ];

    protected $casts = [
        'birth_year' => 'integer',
    ];

    /**
     * Sensor readings recorded for this cat.
     */
    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class)->orderBy('read_at', 'desc');
    }

    /**
     * Alerts triggered for this cat.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class)->orderBy('created_at', 'desc');
    }

    /**
     * Per-cat threshold overrides (if any).
     */
    public function threshold(): HasOne
    {
        return $this->hasOne(Threshold::class);
    }

    /**
     * The most recent sensor reading for this cat.
     */
    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('read_at');
    }

    /**
     * Unacknowledged alerts for this cat.
     */
    public function activeAlerts(): HasMany
    {
        return $this->hasMany(Alert::class)->whereNull('acknowledged_at');
    }

    /**
     * Recompute and persist the cat's status from its latest reading.
     * Returns the new status string.
     */
    public function recomputeStatus(): string
    {
        $reading = $this->latestReading;

        if (! $reading) {
            $status = 'healthy';
        } else {
            $status = $reading->evaluateStatus($this);
        }

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }

        return $status;
    }
}
