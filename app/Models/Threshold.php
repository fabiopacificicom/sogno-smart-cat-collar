<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Threshold extends Model
{
    use HasFactory;

    protected $table = 'thresholds';

    protected $fillable = [
        'cat_id',
        'vital',
        'warning_value',
        'critical_value',
    ];

    protected $casts = [
        'warning_value' => 'float',
        'critical_value' => 'float',
        'cat_id' => 'integer',
    ];

    /**
     * The cat this threshold applies to (null = global default).
     */
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }

    /**
     * Resolve the effective threshold for a given cat and vital.
     * Falls back to the global default (cat_id = null) if no per-cat override exists.
     *
     * @param  int|null  $catId
     * @param  string  $vital  temperature|bpm
     */
    public static function forCat(?int $catId, string $vital): ?self
    {
        if ($catId !== null) {
            $perCat = static::where('cat_id', $catId)->where('vital', $vital)->first();
            if ($perCat) {
                return $perCat;
            }
        }

        return static::whereNull('cat_id')->where('vital', $vital)->first();
    }
}
