<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\ClientPermission;
use App\Enums\PlanModule;
use App\Models\ClientRole;
use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_permission_and_group_is_translated_in_both_languages(): void
    {
        foreach (['ar', 'en'] as $locale) {
            foreach (AdminPermission::cases() as $permission) {
                $this->assertNotSame('permissions.admin.'.$permission->value, __('permissions.admin.'.$permission->value, locale: $locale));
                $this->assertArrayHasKey($permission->group(), __('permissions.admin_groups', locale: $locale));
            }

            foreach (ClientPermission::cases() as $permission) {
                $this->assertNotSame('permissions.client.'.$permission->value, __('permissions.client.'.$permission->value, locale: $locale));
                $this->assertArrayHasKey($permission->group(), __('permissions.client_groups', locale: $locale));
            }
        }
    }

    public function test_module_permissions_follow_the_plan(): void
    {
        $this->seed(PlanSeeder::class);
        $haccp = Plan::where('code', Plan::HACCP)->first();
        $iso = Plan::where('code', Plan::ISO)->first();

        $this->assertSame(PlanModule::Iso, ClientPermission::IsoAccess->module());
        $this->assertFalse(ClientPermission::IsoAccess->isAvailableOn($haccp));
        $this->assertTrue(ClientPermission::IsoAccess->isAvailableOn($iso));
        $this->assertTrue(ClientPermission::PrpAccess->isAvailableOn($haccp));
        $this->assertTrue(ClientPermission::UsersManage->isAvailableOn(null));
        $this->assertFalse(ClientPermission::PrpAccess->isAvailableOn(null));

        $this->assertNotContains(ClientPermission::IsoAccess, ClientPermission::availableOn($haccp));
        $this->assertCount(count(ClientPermission::cases()), ClientPermission::availableOn($iso));
    }

    public function test_default_client_roles_match_business_rules(): void
    {
        $manager = ClientRole::DEFAULTS['branch_manager'];
        $user = ClientRole::DEFAULTS['branch_user'];

        // §3: managers delete and manage products/links; users do neither; neither manages branches, users or PRP texts.
        $this->assertContains(ClientPermission::RecordsDelete, $manager);
        $this->assertContains(ClientPermission::ProductsManage, $manager);
        $this->assertNotContains(ClientPermission::RecordsDelete, $user);
        $this->assertNotContains(ClientPermission::ProductsManage, $user);

        foreach ([ClientPermission::BranchesManage, ClientPermission::UsersManage, ClientPermission::RolesManage, ClientPermission::PrpCustomize] as $ownerOnly) {
            $this->assertNotContains($ownerOnly, $manager);
            $this->assertNotContains($ownerOnly, $user);
        }
    }
}
