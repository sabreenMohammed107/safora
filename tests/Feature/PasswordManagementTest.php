<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientUser;
use App\Models\Plan;
use App\Models\Subscription;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    private function activeOwner(): ClientUser
    {
        $client = Client::factory()->active()->create();
        ClientBranch::factory()->for($client)->create();
        Subscription::factory()->for($client)->create(['plan_id' => Plan::where('code', Plan::HACCP)->value('id')]);

        return ClientUser::factory()->for($client)->create();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
    }

    public function test_admin_resets_client_password_and_user_must_change_it(): void
    {
        $owner = $this->activeOwner();
        $owner->forceFill(['remember_token' => 'old-token'])->save();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.clients.users.password', [$owner->client, $owner]))
            ->assertSessionHas('credentials');

        $temporary = session('credentials')['password'];
        $owner->refresh();
        $this->assertTrue($owner->must_change_password);
        $this->assertNotSame('old-token', $owner->remember_token);
        $this->assertSame('password_reset', $owner->client->activityLogs()->first()->event);

        // The client signs in with the temporary password and is held on "My account".
        $this->post(route('portal.login.store'), ['email' => $owner->email, 'password' => $temporary]);
        $this->get(route('portal.home'))->assertRedirect(route('portal.account.edit'));
        $this->get(route('portal.account.edit'))->assertOk()->assertSee(__('accounts.must_change_password'));

        $this->put(route('portal.account.password'), [
            'current_password' => $temporary, 'password' => 'NewSecret123', 'password_confirmation' => 'NewSecret123',
        ])->assertRedirect(route('portal.home'));

        $this->assertFalse($owner->fresh()->must_change_password);
        $this->get(route('portal.home'))->assertOk();
    }

    public function test_own_password_change_requires_current_password(): void
    {
        $owner = $this->activeOwner();

        $this->actingAs($owner, 'client')
            ->put(route('portal.account.password'), ['current_password' => 'wrong', 'password' => 'NewSecret123', 'password_confirmation' => 'NewSecret123'])
            ->assertSessionHasErrors('current_password');

        $this->put(route('portal.account.password'), ['current_password' => 'password', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors('password');
    }

    public function test_impersonating_admin_cannot_change_client_password_and_is_not_forced(): void
    {
        $owner = $this->activeOwner();
        $owner->forceFill(['must_change_password' => true])->save();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.clients.impersonate', $owner->client))
            ->assertRedirect(route('portal.home'));

        $this->get(route('portal.home'))->assertOk();
        $this->put(route('portal.account.password'), ['current_password' => 'password', 'password' => 'NewSecret123', 'password_confirmation' => 'NewSecret123'])
            ->assertForbidden();
    }

    public function test_staff_reset_and_forced_change_in_admin_area(): void
    {
        $manager = Admin::factory()->create();
        $staff = Admin::factory()->withRole(AdminRole::factory()->granting([AdminPermission::ClientsView])->create())->create();

        $this->actingAs($manager, 'admin')
            ->post(route('admin.staff.password', $staff))
            ->assertSessionHas('credentials');
        $temporary = session('credentials')['password'];

        $this->assertTrue($staff->fresh()->must_change_password);
        $this->post(route('admin.staff.password', $manager))->assertForbidden();

        // The staff member signs in on their own device (a fresh session).
        $this->flushSession();
        $this->actingAs($staff->fresh(), 'admin')
            ->get(route('admin.clients.index'))
            ->assertRedirect(route('admin.profile.edit'));

        $this->put(route('admin.profile.password'), ['current_password' => $temporary, 'password' => 'Fresh12345', 'password_confirmation' => 'Fresh12345'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(Hash::check('Fresh12345', $staff->fresh()->password));
        $this->get(route('admin.clients.index'))->assertOk();
    }

    public function test_new_staff_get_generated_temporary_password(): void
    {
        $role = AdminRole::factory()->create();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.staff.store'), [
                'name' => 'Fahd', 'email' => 'fahd@safora.test', 'locale' => 'ar',
                'admin_role_id' => $role->id, 'is_super_admin' => '0', 'active' => '1',
            ])
            ->assertSessionHas('credentials');

        $staff = Admin::where('email', 'fahd@safora.test')->sole();
        $this->assertTrue($staff->must_change_password);
        $this->assertTrue(Hash::check(session('credentials')['password'], $staff->password));
    }

    public function test_changing_own_password_keeps_current_session_and_ends_others(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
        $this->put(route('admin.profile.password'), ['current_password' => 'password', 'password' => 'Fresh12345', 'password_confirmation' => 'Fresh12345']);

        // This session continues; a session still holding the old password hash is signed out.
        $this->get(route('admin.dashboard'))->assertOk();
        $this->withSession(['password_hash_admin' => 'stale-hash'])->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_profile_details_update(): void
    {
        $admin = Admin::factory()->create(['locale' => 'ar']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), ['name' => 'New Name', 'title' => 'Lead', 'locale' => 'en'])
            ->assertSessionHas('status');

        $this->assertSame('New Name', $admin->fresh()->name);
        $this->assertSame('en', session('locale'));
    }
}
