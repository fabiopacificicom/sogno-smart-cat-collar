<?php

namespace Database\Factories;

use App\Models\Threshold;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Threshold>
 */
class ThresholdFactory extends Factory
{
    protected $model = Threshold::class;

    public function definition(): array
    {
        return [
            'cat_id' => null,
            'vital' => $this->faker->randomElement(['temperature', 'bpm']),
            'warning_value' => 39.0,
            'critical_value' => 39.5,
        ];
    }

    public function temperature(): static
    {
        return $this->state(fn () => [
            'vital' => 'temperature',
            'warning_value' => 39.0,
            'critical_value' => 39.5,
        ]);
    }

    public function bpm(): static
    {
        return $this->state(fn () => [
            'vital' => 'bpm',
            'warning_value' => 220,
            'critical_value' => 250,
        ]);
    }
}
