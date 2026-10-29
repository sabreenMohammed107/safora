<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClientUserRole;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientRole;
use App\Models\ClientUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClientUser>
 */
class ClientUserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'branch_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => ClientUserRole::Owner,
            'password' => static::$password ??= Hash::make('password'),
            'locale' => 'ar',
            'active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * An employee of the branch's company holding the given role (a new role of that company by default).
     */
    public function forBranch(ClientBranch $branch, ?ClientRole $role = null): static
    {
        return $this->state(fn (): array => [
            'client_id' => $branch->client_id,
            'branch_id' => $branch->getKey(),
            'role' => ClientUserRole::Member,
            'client_role_id' => $role?->getKey() ?? ClientRole::factory()->state(['client_id' => $branch->client_id]),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
