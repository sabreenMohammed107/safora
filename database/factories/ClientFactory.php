<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\SectorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company' => fake()->company(),
            'cr_number' => fake()->unique()->numerify('10########'),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('+966 5# ### ####'),
            'sector_category_id' => SectorCategory::factory(),
            'status' => ClientStatus::Pending,
            'registered_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => ClientStatus::Active,
            'approved_at' => now(),
        ]);
    }

    public function suspended(string $reason = 'Overdue invoice'): static
    {
        return $this->state(fn (): array => [
            'status' => ClientStatus::Suspended,
            'status_reason' => $reason,
        ]);
    }

    public function rejected(string $reason = 'CR number mismatch'): static
    {
        return $this->state(fn (): array => [
            'status' => ClientStatus::Rejected,
            'status_reason' => $reason,
        ]);
    }
}
