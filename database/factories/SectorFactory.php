<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sector>
 */
class SectorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->bothify('Sector-####'),
            'name' => ['ar' => 'قطاع '.fake()->word(), 'en' => fake()->words(2, true)],
            'active' => true,
        ];
    }
}
