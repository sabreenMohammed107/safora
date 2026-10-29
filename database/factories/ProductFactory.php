<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'PRD-'.fake()->unique()->numerify('####'),
            'sector_id' => Sector::factory(),
            'name' => ['ar' => 'منتج '.fake()->word(), 'en' => fake()->words(2, true)],
            'active' => true,
        ];
    }
}
