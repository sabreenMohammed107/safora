<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClientPermission;
use App\Models\Client;
use App\Models\ClientRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientRole>
 */
class ClientRoleFactory extends Factory
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
            'key' => null,
            'name' => fake()->unique()->jobTitle(),
            'permissions' => [ClientPermission::RecordsView],
        ];
    }

    /**
     * @param  list<ClientPermission>  $permissions
     */
    public function granting(array $permissions): static
    {
        return $this->state(fn (): array => ['permissions' => $permissions]);
    }
}
