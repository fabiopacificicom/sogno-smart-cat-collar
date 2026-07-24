<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'key',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get a single setting value for a provider.
     */
    public static function get(string $provider, string $key, mixed $default = null): mixed
    {
        $setting = static::where('provider', $provider)->where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    /**
     * Set a single setting value for a provider.
     */
    public static function set(string $provider, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['provider' => $provider, 'key' => $key],
            ['value' => $value],
        );
    }

    /**
     * Get all settings for a provider as a key => value map.
     */
    public static function allFor(string $provider): array
    {
        return static::where('provider', $provider)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Mark a provider as the active data source.
     */
    public static function activate(string $provider): void
    {
        // Deactivate all providers first
        static::query()->update(['is_active' => false]);

        // Activate the chosen one (create a stub row if it doesn't exist yet)
        $rows = static::where('provider', $provider)->get();
        if ($rows->isEmpty()) {
            static::create([
                'provider' => $provider,
                'key' => 'active',
                'value' => '1',
                'is_active' => true,
            ]);
        } else {
            static::where('provider', $provider)->update(['is_active' => true]);
        }
    }

    /**
     * The currently active provider key (e.g. "mock", "direct_api", "telegram").
     */
    public static function activeProvider(): ?string
    {
        return static::where('is_active', true)->value('provider')
            ?? static::get('mock', 'active_provider', 'mock');
    }
}
