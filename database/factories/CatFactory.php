<?php

namespace Database\Factories;

use App\Models\Cat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cat>
 */
class CatFactory extends Factory
{
    protected $model = Cat::class;

    public function definition(): array
    {
        $names = ['Antifa', 'Anakin', 'Mando', 'Grogu', 'Gaza', 'Jabba', 'Sabbia', 'Luna', 'Milo', 'Cleo'];
        $breeds = ['Domestic Shorthair', 'Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Ragdoll', 'Bengal', 'Sphynx'];

        return [
            'name' => $this->faker->unique()->randomElement($names),
            'breed' => $this->faker->randomElement($breeds),
            'photo_path' => null,
            'birth_year' => $this->faker->numberBetween(2015, 2024),
            'status' => 'healthy',
        ];
    }

    public function healthy(): static
    {
        return $this->state(fn () => ['status' => 'healthy']);
    }

    public function warning(): static
    {
        return $this->state(fn () => ['status' => 'warning']);
    }

    public function critical(): static
    {
        return $this->state(fn () => ['status' => 'critical']);
    }
}
