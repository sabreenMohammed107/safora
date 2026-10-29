<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
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
            'name' => fake()->name(),
            'title' => 'System Administrator',
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'locale' => 'ar',
            'is_super_admin' => true,
            'active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Regular staff holding the given role (a new role granting only clients.view by default).
     */
    public function withRole(?AdminRole $role = null): static
    {
        return $this->state(fn (): array => [
            'is_super_admin' => false,
            'admin_role_id' => $role?->getKey() ?? AdminRole::factory(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
