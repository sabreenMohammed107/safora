<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory()->active(),
            'plan_id' => Plan::factory(),
            'cycle' => BillingCycle::Monthly,
            'discount' => 0,
            'start_date' => today(),
            'end_date' => today()->addMonth(),
        ];
    }

    public function annual(): static
    {
        return $this->state(fn (): array => [
            'cycle' => BillingCycle::Annual,
            'end_date' => today()->addYear(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'start_date' => today()->subMonths(2),
            'end_date' => today()->subMonth(),
        ]);
    }
}
