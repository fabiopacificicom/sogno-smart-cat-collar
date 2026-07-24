<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Cat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        $vital = $this->faker->randomElement(['temperature', 'bpm', 'activity']);
        $type = $this->faker->randomElement(['warning', 'critical', 'info']);

        $value = match ($vital) {
            'temperature' => $this->faker->randomFloat(1, 39.0, 40.5).'°C',
            'bpm' => (string) $this->faker->numberBetween(220, 280),
            default => $this->faker->randomElement(['low', 'medium', 'high']),
        };

        $threshold = match ($vital) {
            'temperature' => $this->faker->randomFloat(1, 39.0, 39.5),
            'bpm' => (float) $this->faker->numberBetween(220, 250),
            default => null,
        };

        return [
            'cat_id' => Cat::factory(),
            'type' => $type,
            'vital' => $vital,
            'value' => $value,
            'threshold' => $threshold,
            'message' => ucfirst($vital).' '.$value.' exceeds '.$type.' threshold',
            'acknowledged_at' => null,
        ];
    }
}
