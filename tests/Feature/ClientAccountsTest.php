<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\ClientUserRole;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientRole;
use App\Models\ClientUser;
use App\Models\Plan;
use App\Models\Subscription;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Branch and user management from the admin client profile and the portal, with the shared rules.
 */
class ClientAccountsTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private ClientBranch $main;

    private ClientUser $owner;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->client = Client::factory()->active()->create();
        $this->main = ClientBranch::factory()->for($this->client)->create(['code' => 'BR-01']);
        $this->owner = ClientUser::factory()->for($this->client)->create();
        Subscription::factory()->for($this->client)->create(['plan_id' => Plan::where('code', Plan::HACCP)->value('id')]);
        ClientRole::createDefaultsFor($this->client);
        $this->admin = Admin::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function branchPayload(array $overrides = []): array
    {
        return ['name' => 'Jeddah', 'city' => 'Jeddah', 'manager' => 'Laila', 'phone' => '0500000000', 'active' => '1', ...$overrides];
    }

    public function test_admin_adds_branch_with_next_code_and_log(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.branches.store', $this->client), $this->branchPayload())
            ->assertRedirect(route('admin.clients.show', $this->client));

        $branch = $this->client->branches()->where('name', 'Jeddah')->sole();
        $this->assertSame('BR-02', $branch->code);
        $this->assertSame('branch_created', $this->client->activityLogs()->first()->event);
    }

    public function test_last_active_branch_cannot_be_deactivated(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.clients.branches.update', [$this->client, $this->main]), $this->branchPayload(['name' => $this->main->name, 'active' => '0']))
            ->assertSessionHasErrors(['active' => __('accounts.errors.last_branch')]);

        $this->assertTrue($this->main->fresh()->active);
    }

    public function test_branch_can_be_deactivated_when_another_is_active(): void
    {
        ClientBranch::factory()->for($this->client)->create();

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.clients.branches.update', [$this->client, $this->main]), $this->branchPayload(['active' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->main->fresh()->active);
        $this->assertSame('branch_deactivated', $this->client->activityLogs()->first()->event);
    }

    public function test_plan_branch_limit_is_enforced(): void
    {
        Plan::where('code', Plan::HACCP)->update(['max_sites' => 1]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.branches.store', $this->client), $this->branchPayload())
            ->assertSessionHasErrors(['active' => __('accounts.errors.branch_limit', ['max' => 1])]);

        // An inactive branch does not count against the limit.
        $this->post(route('admin.clients.branches.store', $this->client), $this->branchPayload(['active' => '0']))
            ->assertSessionHasNoErrors();
    }

    public function test_branch_routes_are_scoped_to_the_client(): void
    {
        $other = ClientBranch::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients.branches.edit', [$this->client, $other]))
            ->assertNotFound();
    }

    public function test_admin_adds_user_with_generated_temporary_password_shown_once(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.users.store', $this->client), [
                'name' => 'Laila', 'email' => 'laila@example.com', 'role' => 'member',
                'branch_id' => $this->main->id, 'client_role_id' => $this->client->roles()->value('id'), 'active' => '1',
            ])
            ->assertRedirect(route('admin.clients.show', $this->client));

        $credentials = session('credentials');
        $this->assertSame('laila@example.com', $credentials['email']);
        $this->assertNotEmpty($credentials['password']);

        $user = ClientUser::where('email', 'laila@example.com')->sole();
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(password_verify($credentials['password'], $user->password));

        $this->get(route('admin.clients.show', $this->client))->assertSee($credentials['password']);
        $this->get(route('admin.clients.show', $this->client))->assertDontSee($credentials['password']);
    }

    public function test_last_owner_cannot_be_demoted_or_deactivated_by_admin(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.clients.users.update', [$this->client, $this->owner]), [
                'name' => $this->owner->name, 'email' => $this->owner->email, 'role' => 'owner', 'active' => '0',
            ])
            ->assertSessionHasErrors(['role' => __('accounts.errors.last_owner')]);

        $this->assertTrue($this->owner->fresh()->active);
    }

    public function test_admin_can_appoint_second_owner_then_demote_first(): void
    {
        $second = ClientUser::factory()->for($this->client)->create(['role' => ClientUserRole::Owner]);

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.clients.users.update', [$this->client, $this->owner]), [
                'name' => $this->owner->name, 'email' => $this->owner->email, 'role' => 'member',
                'branch_id' => $this->main->id, 'client_role_id' => $this->client->roles()->value('id'), 'active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->owner->fresh()->isOwner());
        $this->assertTrue($second->fresh()->isOwner());
    }

    public function test_plan_user_limit_is_enforced(): void
    {
        Plan::where('code', Plan::HACCP)->update(['max_users' => 1]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.users.store', $this->client), [
                'name' => 'Extra', 'email' => 'extra@example.com', 'role' => 'owner', 'active' => '1',
            ])
            ->assertSessionHasErrors(['active' => __('accounts.errors.user_limit', ['max' => 1])]);
    }

    public function test_account_management_requires_permission(): void
    {
        $viewer = Admin::factory()->withRole(AdminRole::factory()->granting([AdminPermission::ClientsView])->create())->create();

        $this->actingAs($viewer, 'admin');
        $this->get(route('admin.clients.show', $this->client))->assertOk()->assertDontSee(route('admin.clients.branches.create', $this->client));
        $this->get(route('admin.clients.branches.create', $this->client))->assertForbidden();
        $this->post(route('admin.clients.users.password', [$this->client, $this->owner]))->assertForbidden();
    }

    public function test_portal_owner_manages_branches_with_cost_note(): void
    {
        $this->actingAs($this->owner, 'client')
            ->get(route('portal.branches.index'))
            ->assertOk()
            ->assertSee('+57.50'); // HACCP monthly extra branch: 50 SAR + 15% VAT

        $this->post(route('portal.branches.store'), $this->branchPayload())->assertRedirect(route('portal.branches.index'));
        $this->assertSame(2, $this->client->branches()->count());

        $this->put(route('portal.branches.update', $this->client->branches()->where('code', 'BR-02')->first()), $this->branchPayload(['active' => '0']))
            ->assertSessionHasNoErrors();
        $this->put(route('portal.branches.update', $this->main), $this->branchPayload(['active' => '0']))
            ->assertSessionHasErrors('active');
    }

    public function test_portal_branches_require_permission_and_are_tenant_scoped(): void
    {
        $member = ClientUser::factory()->forBranch($this->main, $this->client->roles()->where('key', 'branch_manager')->first())->create();
        $this->actingAs($member, 'client')->get(route('portal.branches.index'))->assertForbidden();

        $foreign = ClientBranch::factory()->create();
        $this->actingAs($this->owner, 'client')->get(route('portal.branches.edit', $foreign))->assertNotFound();
    }
}
