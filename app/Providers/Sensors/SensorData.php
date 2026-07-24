<?php

namespace App\Providers\Sensors;

/**
 * A plain value object representing a single sensor reading from the collar.
 */
class SensorData
{
    public function __construct(
        public readonly ?int $catId,
        public readonly float $temperature,
        public readonly int $bpm,
        public readonly string $activity, // low|medium|high
        public readonly string $source,   // direct_api|telegram|mock
        public readonly ?string $deviceId = null,
        public readonly ?\DateTimeInterface $readAt = null,
    ) {}

    /**
     * Build a SensorData from an associative array (e.g. JSON API payload).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            catId: $data['cat_id'] ?? null,
            temperature: (float) $data['temperature'],
            bpm: (int) $data['bpm'],
            activity: $data['activity'] ?? 'medium',
            source: $data['source'] ?? 'mock',
            deviceId: $data['device_id'] ?? null,
            readAt: isset($data['timestamp']) ? new \DateTimeImmutable($data['timestamp']) : null,
        );
    }
}
