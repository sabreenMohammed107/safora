<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlanTier;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $monthly = fake()->randomElement([100, 150, 200]);

        return [
            'code' => 'PLN-'.fake()->unique()->bothify('????##'),
            'name' => ['ar' => 'خطة '.fake()->word(), 'en' => fake()->words(2, true)],
            'tier' => PlanTier::Pro,
            'price_monthly' => $monthly,
            'price_annual' => $monthly * 12,
            'included_branches' => 1,
            'extra_branch_monthly' => $monthly / 2,
            'extra_branch_annual' => $monthly * 6,
            'max_sites' => null,
            'max_users' => null,
            'active' => true,
        ];
    }
}
