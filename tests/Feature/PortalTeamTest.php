<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ClientPermission;
use App\Enums\ClientUserRole;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientRole;
use App\Models\ClientUser;
use App\Models\Plan;
use App\Models\Subscription;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PortalTeamTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private ClientBranch $branch;

    private ClientUser $owner;

    /** @var array<string, ClientRole> */
    private array $defaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        [$this->client, $this->branch, $this->owner, $this->defaults] = $this->company(Plan::HACCP);
    }

    /**
     * @return array{Client, ClientBranch, ClientUser, array<string, ClientRole>}
     */
    private function company(string $planCode): array
    {
        $client = Client::factory()->active()->create();
        $branch = ClientBranch::factory()->for($client)->create();
        $owner = ClientUser::factory()->for($client)->create();
        Subscription::factory()->for($client)->create(['plan_id' => Plan::where('code', $planCode)->value('id')]);

        return [$client, $branch, $owner, ClientRole::createDefaultsFor($client)];
    }

    private function member(ClientRole $role): ClientUser
    {
        return ClientUser::factory()->forBranch($this->branch, $role)->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function memberPayload(array $overrides = []): array
    {
        return [
            'name' => 'Laila', 'email' => 'laila@example.com',
            'password' => 'Secret123', 'password_confirmation' => 'Secret123',
            'role' => 'member', 'branch_id' => $this->branch->id,
            'client_role_id' => $this->defaults['branch_user']->id, 'active' => '1',
            ...$overrides,
        ];
    }

    public function test_effective_permissions_follow_role_and_plan(): void
    {
        $manager = $this->member($this->defaults['branch_manager']);
        $user = $this->member($this->defaults['branch_user']);

        $this->assertTrue(Gate::forUser($manager)->allows('records.delete'));
        $this->assertFalse(Gate::forUser($user)->allows('records.delete'));
        $this->assertFalse(Gate::forUser($manager)->allows('users.manage'));

        // The HACCP plan has no ISO module: nobody holds iso.access, not even the owner.
        $this->assertTrue($this->defaults['branch_manager']->grants(ClientPermission::IsoAccess));
        $this->assertFalse(Gate::forUser($manager)->allows('iso.access'));
        $this->assertFalse(Gate::forUser($this->owner)->allows('iso.access'));
        $this->assertTrue(Gate::forUser($this->owner)->allows('users.manage'));
    }

    public function test_owner_sees_team_page_and_members_without_permission_do_not(): void
    {
        $this->actingAs($this->owner, 'client')->get(route('portal.home'))->assertSee(route('portal.team.index'));
        $this->get(route('portal.team.index'))->assertOk()->assertSee(__('portal.default_roles.branch_manager'));

        $member = $this->member($this->defaults['branch_manager']);
        $this->actingAs($member, 'client')->get(route('portal.home'))->assertDontSee(route('portal.team.index'));
        $this->get(route('portal.team.index'))->assertForbidden();
        $this->get(route('portal.team.roles.create'))->assertForbidden();
    }

    public function test_owner_creates_role_within_plan_bounds(): void
    {
        $this->actingAs($this->owner, 'client');

        $this->post(route('portal.team.roles.store'), ['name' => 'Auditor', 'permissions' => ['records.view', 'iso.access']])
            ->assertSessionHasErrors('permissions.1');

        $this->post(route('portal.team.roles.store'), ['name' => 'Auditor', 'permissions' => ['records.view', 'prp.access']])
            ->assertRedirect(route('portal.team.index'));

        $role = $this->client->roles()->where('name', 'Auditor')->sole();
        $this->assertEqualsCanonicalizing([ClientPermission::RecordsView, ClientPermission::PrpAccess], $role->permissions->all());
        $this->assertSame('role_created', $this->client->activityLogs()->first()->event);
    }

    public function test_editing_a_role_keeps_permissions_outside_the_plan_and_renaming_clears_default_key(): void
    {
        $role = $this->defaults['branch_manager'];

        $this->actingAs($this->owner, 'client')
            ->put(route('portal.team.roles.update', $role), ['name' => 'Site lead', 'permissions' => ['records.view']])
            ->assertRedirect(route('portal.team.index'));

        $role->refresh();
        $this->assertNull($role->key);
        $this->assertSame('Site lead', $role->displayName());
        $this->assertEqualsCanonicalizing([ClientPermission::RecordsView, ClientPermission::IsoAccess], $role->permissions->all());
    }

    public function test_owner_adds_an_employee_who_can_sign_in(): void
    {
        $this->actingAs($this->owner, 'client')
            ->post(route('portal.team.members.store'), $this->memberPayload())
            ->assertRedirect(route('portal.team.index'));

        $member = ClientUser::where('email', 'laila@example.com')->sole();
        $this->assertSame(ClientUserRole::Member, $member->role);
        $this->assertTrue($member->clientRole->is($this->defaults['branch_user']));
        $this->assertSame($this->branch->id, $member->branch_id);
        $this->assertSame(__('portal.default_roles.branch_user'), $member->roleLabel());
    }

    public function test_members_need_branch_and_role(): void
    {
        $this->actingAs($this->owner, 'client')
            ->post(route('portal.team.members.store'), $this->memberPayload(['branch_id' => '', 'client_role_id' => '']))
            ->assertSessionHasErrors(['branch_id', 'client_role_id']);
    }

    public function test_tenant_isolation(): void
    {
        [, $otherBranch, $otherOwner, $otherRoles] = $this->company(Plan::ISO);

        $this->actingAs($this->owner, 'client');

        $this->get(route('portal.team.roles.edit', $otherRoles['branch_user']))->assertNotFound();
        $this->get(route('portal.team.members.edit', $otherOwner))->assertNotFound();
        $this->delete(route('portal.team.roles.destroy', $otherRoles['branch_user']))->assertNotFound();

        $this->post(route('portal.team.members.store'), $this->memberPayload([
            'branch_id' => $otherBranch->id,
            'client_role_id' => $otherRoles['branch_user']->id,
        ]))->assertSessionHasErrors(['branch_id', 'client_role_id']);
    }

    public function test_user_manager_cannot_appoint_or_edit_owners(): void
    {
        $hr = $this->member(ClientRole::factory()->for($this->client)->granting([ClientPermission::UsersManage])->create());

        $this->actingAs($hr, 'client');
        $this->get(route('portal.team.index'))->assertOk();
        $this->get(route('portal.team.members.edit', $this->owner))->assertForbidden();

        $this->post(route('portal.team.members.store'), $this->memberPayload(['role' => 'owner', 'branch_id' => '', 'client_role_id' => '']))
            ->assertSessionHasErrors('role');
    }

    public function test_role_manager_cannot_grant_permissions_they_lack(): void
    {
        $lead = $this->member(ClientRole::factory()->for($this->client)->granting([ClientPermission::RolesManage, ClientPermission::RecordsView])->create());

        $this->actingAs($lead, 'client')
            ->post(route('portal.team.roles.store'), ['name' => 'Deleters', 'permissions' => ['records.view', 'records.delete']])
            ->assertSessionHasErrors('permissions.1');

        $this->get(route('portal.team.roles.edit', $this->defaults['branch_manager']))->assertForbidden();
    }

    public function test_last_owner_is_protected(): void
    {
        $this->actingAs($this->owner, 'client')
            ->put(route('portal.team.members.update', $this->owner), $this->memberPayload([
                'name' => $this->owner->name, 'email' => $this->owner->email, 'password' => '', 'password_confirmation' => '',
            ]))
            ->assertSessionHasErrors(['active' => __('portal.team.errors.self_lockout')]);

        $this->assertTrue($this->owner->fresh()->isOwner());
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $this->member($this->defaults['branch_user']);
        $unused = ClientRole::factory()->for($this->client)->create();

        $this->actingAs($this->owner, 'client');

        $this->delete(route('portal.team.roles.destroy', $this->defaults['branch_user']))->assertSessionHas('error');
        $this->assertModelExists($this->defaults['branch_user']);

        $this->delete(route('portal.team.roles.destroy', $unused))->assertRedirect(route('portal.team.index'));
        $this->assertModelMissing($unused);
    }

    public function test_registration_creates_default_roles(): void
    {
        $this->assertSame(['branch_manager', 'branch_user'], $this->client->roles()->orderBy('id')->pluck('key')->all());
    }
}
