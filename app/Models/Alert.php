<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_id',
        'type',
        'vital',
        'value',
        'threshold',
        'message',
        'acknowledged_at',
    ];

    protected $casts = [
        'threshold' => 'float',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * The cat this alert belongs to.
     */
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }

    /**
     * Scope to only unacknowledged alerts.
     */
    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    /**
     * Scope to only active (critical/warning) alerts.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('type', ['critical', 'warning'])->whereNull('acknowledged_at');
    }

    /**
     * Mark this alert as acknowledged.
     */
    public function acknowledge(): void
    {
        if ($this->acknowledged_at === null) {
            $this->update(['acknowledged_at' => now()]);
        }
    }
}
