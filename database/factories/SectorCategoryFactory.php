<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SectorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SectorCategory>
 */
class SectorCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CAT-'.fake()->unique()->numerify('###'),
            'name' => ['ar' => 'تصنيف '.fake()->word(), 'en' => fake()->words(2, true)],
            'icon' => 'fa-building',
            'active' => true,
        ];
    }
}
