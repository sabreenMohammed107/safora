<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Seeder;

/**
 * Local demo staff roles and accounts for trying the permission system.
 * Password for every account: Safora@2026.
 */
class DemoStaffSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'مراجع العملاء' => [
                'description' => 'مراجعة طلبات التسجيل واعتمادها',
                'permissions' => [AdminPermission::ClientsView, AdminPermission::ClientsApprove, AdminPermission::ClientsDocuments],
                'staff' => ['reviewer@safora.test', 'نورة المراجِعة', 'أخصائية اعتماد العملاء'],
            ],
            'الدعم الفني' => [
                'description' => 'متابعة العملاء والدخول كعميل للمساعدة',
                'permissions' => [AdminPermission::ClientsView, AdminPermission::ClientsImpersonate],
                'staff' => ['support@safora.test', 'فهد الدعم', 'أخصائي دعم فني'],
            ],
        ];

        foreach ($roles as $name => $definition) {
            $role = AdminRole::firstOrCreate(['name' => $name], [
                'description' => $definition['description'],
                'permissions' => $definition['permissions'],
            ]);

            [$email, $staffName, $title] = $definition['staff'];

            Admin::firstOrCreate(['email' => $email], [
                'name' => $staffName,
                'title' => $title,
                'password' => AdminSeeder::DEMO_PASSWORD,
                'locale' => 'ar',
                'admin_role_id' => $role->id,
                'active' => true,
            ]);
        }
    }
}
