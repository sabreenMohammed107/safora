<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolesAndStaffTest extends TestCase
{
    use RefreshDatabase;

    private function staffWith(array $permissions): Admin
    {
        return Admin::factory()->withRole(AdminRole::factory()->granting($permissions)->create())->create();
    }

    public function test_permissions_gate_client_routes(): void
    {
        $client = Client::factory()->create();
        $viewer = $this->staffWith([AdminPermission::ClientsView]);

        $this->actingAs($viewer, 'admin');
        $this->get(route('admin.clients.index'))->assertOk();
        $this->get(route('admin.clients.show', $client))->assertOk()->assertDontSee(route('admin.clients.approve', $client));
        $this->post(route('admin.clients.reject', $client), ['reason' => 'x'])->assertForbidden();
        $this->post(route('admin.clients.impersonate', $client))->assertForbidden();
        $this->get(route('admin.staff.index'))->assertForbidden();
    }

    public function test_staff_without_client_access_sees_limited_dashboard(): void
    {
        $this->actingAs($this->staffWith([AdminPermission::PlansManage]), 'admin');

        $this->get(route('admin.dashboard'))->assertOk()->assertSee(__('admin.dashboard.limited_title'));
        $this->get(route('admin.clients.index'))->assertForbidden();
    }

    public function test_reviewer_can_approve(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->staffWith([AdminPermission::ClientsView, AdminPermission::ClientsApprove]), 'admin')
            ->post(route('admin.clients.reject', $client), ['reason' => 'Mismatch'])
            ->assertRedirect(route('admin.clients.show', $client));
    }

    public function test_deactivated_staff_are_signed_out(): void
    {
        $staff = $this->staffWith([AdminPermission::ClientsView]);
        $staff->update(['active' => false]);

        $this->actingAs($staff, 'admin')->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_super_admin_creates_role_and_staff(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->get(route('admin.roles.create'))->assertOk();
        $this->post(route('admin.roles.store'), [
            'name' => 'Reviewers',
            'permissions' => ['clients.view', 'clients.approve'],
        ])->assertRedirect(route('admin.staff.index'));

        $role = AdminRole::where('name', 'Reviewers')->sole();
        $this->assertEqualsCanonicalizing([AdminPermission::ClientsView, AdminPermission::ClientsApprove], $role->permissions->all());

        $this->post(route('admin.staff.store'), [
            'name' => 'Noura', 'email' => 'noura@safora.test', 'locale' => 'ar',
            'password' => 'Secret123', 'password_confirmation' => 'Secret123',
            'admin_role_id' => $role->id, 'is_super_admin' => '0', 'active' => '1',
        ])->assertRedirect(route('admin.staff.index'));

        $staff = Admin::where('email', 'noura@safora.test')->sole();
        $this->assertFalse($staff->is_super_admin);
        $this->assertTrue($staff->hasPermission(AdminPermission::ClientsApprove));
        $this->assertFalse($staff->hasPermission(AdminPermission::StaffManage));
    }

    public function test_staff_cannot_grant_permissions_they_lack(): void
    {
        $this->actingAs($this->staffWith([AdminPermission::RolesManage, AdminPermission::ClientsView]), 'admin');

        $this->post(route('admin.roles.store'), ['name' => 'Too strong', 'permissions' => ['clients.view', 'clients.approve']])
            ->assertSessionHasErrors('permissions.1');

        $stronger = AdminRole::factory()->granting([AdminPermission::ClientsApprove])->create();
        $this->get(route('admin.roles.edit', $stronger))->assertForbidden();
    }

    public function test_only_super_admins_manage_super_admins(): void
    {
        $manager = $this->staffWith([AdminPermission::StaffManage]);
        $superAdmin = Admin::factory()->create();
        $role = AdminRole::factory()->create();

        $this->actingAs($manager, 'admin');
        $this->get(route('admin.staff.edit', $superAdmin))->assertForbidden();

        $this->post(route('admin.staff.store'), [
            'name' => 'Sneaky', 'email' => 'sneaky@safora.test', 'locale' => 'en',
            'password' => 'Secret123', 'password_confirmation' => 'Secret123',
            'is_super_admin' => '1', 'active' => '1',
        ])->assertSessionHasErrors('is_super_admin');

        $this->assertDatabaseMissing('admins', ['email' => 'sneaky@safora.test']);
    }

    public function test_is_super_admin_cannot_be_mass_assigned(): void
    {
        $admin = new Admin(['name' => 'x', 'email' => 'x@safora.test', 'password' => 'x', 'is_super_admin' => true]);

        $this->assertNull($admin->is_super_admin);
    }

    public function test_last_super_admin_cannot_be_demoted_or_lock_themselves_out(): void
    {
        $superAdmin = Admin::factory()->create();
        $role = AdminRole::factory()->create();

        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.staff.update', $superAdmin), [
                'name' => $superAdmin->name, 'email' => $superAdmin->email, 'locale' => 'ar',
                'admin_role_id' => $role->id, 'is_super_admin' => '0', 'active' => '1',
            ])
            ->assertSessionHasErrors(['is_super_admin', 'active']);

        $this->assertTrue($superAdmin->fresh()->is_super_admin);
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $used = AdminRole::factory()->create();
        Admin::factory()->withRole($used)->create();
        $unused = AdminRole::factory()->create();

        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->delete(route('admin.roles.destroy', $used))->assertSessionHas('error');
        $this->assertModelExists($used);

        $this->delete(route('admin.roles.destroy', $unused))->assertRedirect(route('admin.staff.index'));
        $this->assertModelMissing($unused);
    }

    public function test_staff_page_lists_staff_and_roles(): void
    {
        $role = AdminRole::factory()->create(['name' => 'Support desk']);
        Admin::factory()->withRole($role)->create(['name' => 'Fahd']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.staff.index'))
            ->assertOk()
            ->assertSee('Fahd')
            ->assertSee('Support desk');
    }
}
