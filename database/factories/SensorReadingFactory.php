<?php

namespace Database\Factories;

use App\Models\Cat;
use App\Models\SensorReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensorReading>
 */
class SensorReadingFactory extends Factory
{
    protected $model = SensorReading::class;

    public function definition(): array
    {
        return [
            'cat_id' => Cat::factory(),
            'temperature' => $this->faker->randomFloat(1, 37.8, 39.2),
            'bpm' => $this->faker->numberBetween(120, 240),
            'activity' => $this->faker->randomElement(['low', 'medium', 'high']),
            'source' => 'mock',
            'read_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ];
    }

    public function healthy(): static
    {
        return $this->state(fn () => [
            'temperature' => $this->faker->randomFloat(1, 37.8, 38.9),
            'bpm' => $this->faker->numberBetween(120, 210),
            'activity' => $this->faker->randomElement(['medium', 'high']),
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn () => [
            'temperature' => $this->faker->randomFloat(1, 39.0, 39.4),
            'bpm' => $this->faker->numberBetween(220, 249),
            'activity' => $this->faker->randomElement(['low', 'medium']),
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn () => [
            'temperature' => $this->faker->randomFloat(1, 39.5, 40.5),
            'bpm' => $this->faker->numberBetween(250, 280),
            'activity' => 'low',
        ]);
    }
}
