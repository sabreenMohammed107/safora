<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates the initial super admin from config('safora.admin'). Outside production the
 * password defaults to the prototype's demo password; production requires SAFORA_ADMIN_PASSWORD.
 */
class AdminSeeder extends Seeder
{
    public const string DEMO_PASSWORD = 'Safora@2026';

    public function run(): void
    {
        $config = config('safora.admin');

        if (Admin::where('email', $config['email'])->exists()) {
            return;
        }

        $password = $config['password'] ?? (app()->isProduction()
            ? throw new RuntimeException('Set SAFORA_ADMIN_PASSWORD before seeding the administrator in production.')
            : self::DEMO_PASSWORD);

        $admin = new Admin([
            'name' => $config['name'],
            'title' => 'System Administrator',
            'email' => $config['email'],
            'password' => $password,
            'locale' => 'ar',
            'active' => true,
        ]);
        $admin->is_super_admin = true;
        $admin->save();
    }
}
