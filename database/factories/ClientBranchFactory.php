<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientBranch>
 */
class ClientBranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'code' => fake()->unique()->numerify('BR-###'),
            'name' => fake()->city().' Branch',
            'city' => fake()->city(),
            'manager' => fake()->name(),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
