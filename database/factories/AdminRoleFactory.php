<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdminPermission;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminRole>
 */
class AdminRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'permissions' => [AdminPermission::ClientsView],
        ];
    }

    /**
     * @param  list<AdminPermission>  $permissions
     */
    public function granting(array $permissions): static
    {
        return $this->state(fn (): array => ['permissions' => $permissions]);
    }
}
