<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\PortalBlock;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientUser;
use App\Models\Plan;
use App\Models\SectorCategory;
use App\Models\Subscription;
use App\Services\ClientRegistrationService;
use App\Services\PortalSession;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
    }

    /**
     * An active client with a running HACCP subscription, a main branch and an owner.
     *
     * @return array{Client, ClientBranch, ClientUser}
     */
    private function activeClient(): array
    {
        $client = Client::factory()->active()->create();
        $main = ClientBranch::factory()->for($client)->create(['code' => 'BR-01', 'name' => 'Main']);
        $owner = ClientUser::factory()->for($client)->create();
        Subscription::factory()->for($client)->create(['plan_id' => Plan::where('code', Plan::HACCP)->value('id')]);

        return [$client, $main, $owner];
    }

    public function test_registration_page_creates_pending_client_with_login(): void
    {
        Storage::fake(ClientRegistrationService::DOCUMENT_DISK);
        $category = SectorCategory::factory()->create();

        $this->get(route('portal.register'))->assertOk()->assertSee($category->name);

        $this->post(route('portal.register.store'), [
            'company' => 'Waha Hotel',
            'sector_category_id' => $category->id,
            'contact_name' => 'Noura',
            'phone' => '+966 54 222 3344',
            'email' => 'Quality@Waha.example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'cr_number' => '1010 998 877',
            'documents' => [UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf')],
        ])->assertRedirect(route('portal.login'))->assertSessionHas('status');

        $client = Client::where('company', 'Waha Hotel')->sole();
        $this->assertSame('1010998877', $client->cr_number);
        $this->assertSame('quality@waha.example.com', $client->owner->email);

        $this->post(route('portal.login.store'), ['email' => 'quality@waha.example.com', 'password' => 'Secret123'])
            ->assertRedirect(route('portal.home'));

        $this->get(route('portal.home'))->assertRedirect(route('portal.blocked'));
        $this->get(route('portal.blocked'))->assertOk()->assertSee(PortalBlock::Pending->title());
    }

    public function test_active_owner_signs_in_and_sees_home(): void
    {
        [$client, , $owner] = $this->activeClient();

        $this->post(route('portal.login.store'), ['email' => $owner->email, 'password' => 'password'])
            ->assertRedirect(route('portal.home'));

        $this->get(route('portal.home'))
            ->assertOk()
            ->assertSee($client->company)
            ->assertSee(__('portal.home.welcome', ['name' => $owner->name]));

        $this->assertSame('portal_login', $client->activityLogs()->first()->event);
    }

    /**
     * @return array<string, array{\Closure(Client, ClientUser): void, PortalBlock}>
     */
    public static function blockingStates(): array
    {
        return [
            'suspended client' => [fn (Client $client) => $client->update(['status' => ClientStatus::Suspended]), PortalBlock::Suspended],
            'rejected client' => [fn (Client $client) => $client->update(['status' => ClientStatus::Rejected]), PortalBlock::Rejected],
            'no subscription' => [fn (Client $client) => $client->subscriptions()->delete(), PortalBlock::NoPlan],
            'expired subscription' => [fn (Client $client) => $client->subscriptions()->update(['end_date' => today()->subDay()]), PortalBlock::Expired],
            'inactive user' => [fn (Client $client, ClientUser $user) => $user->update(['active' => false]), PortalBlock::UserInactive],
            'no active branch' => [fn (Client $client) => $client->branches()->update(['active' => false]), PortalBlock::NoBranch],
        ];
    }

    #[DataProvider('blockingStates')]
    public function test_portal_is_blocked(\Closure $arrange, PortalBlock $expected): void
    {
        [$client, , $owner] = $this->activeClient();
        $arrange($client, $owner);

        $this->assertSame($expected, $owner->fresh()->portalBlock());

        $this->actingAs($owner->fresh(), 'client')->get(route('portal.home'))->assertRedirect(route('portal.blocked'));
        $this->get(route('portal.blocked'))->assertOk()->assertSee($expected->title());
    }

    public function test_blocked_page_redirects_home_when_access_is_restored(): void
    {
        [, , $owner] = $this->activeClient();

        $this->actingAs($owner, 'client')->get(route('portal.blocked'))->assertRedirect(route('portal.home'));
    }

    public function test_owner_defaults_to_all_branches_and_can_switch(): void
    {
        [$client, $main, $owner] = $this->activeClient();
        $jeddah = ClientBranch::factory()->for($client)->create(['code' => 'BR-02', 'name' => 'Jeddah']);

        $this->actingAs($owner, 'client')->get(route('portal.home'))
            ->assertOk()
            ->assertSee(__('portal.home.compare_heading'))
            ->assertSee('Jeddah');
        $this->assertSame(PortalSession::ALL_BRANCHES, session(PortalSession::BRANCH_KEY));

        $this->put(route('portal.branch.update'), ['branch' => (string) $jeddah->id])->assertRedirect();
        $this->assertSame($jeddah->id, session(PortalSession::BRANCH_KEY));

        $this->get(route('portal.home'))->assertOk()->assertDontSee(__('portal.home.compare_heading'));
    }

    public function test_branch_user_is_confined_to_own_branch(): void
    {
        [$client, $main] = $this->activeClient();
        $jeddah = ClientBranch::factory()->for($client)->create(['code' => 'BR-02', 'name' => 'Jeddah']);
        $user = ClientUser::factory()->forBranch($jeddah)->create();

        $this->actingAs($user, 'client')->get(route('portal.home'))->assertOk();
        $this->assertSame($jeddah->id, session(PortalSession::BRANCH_KEY));

        $this->put(route('portal.branch.update'), ['branch' => (string) $main->id])->assertForbidden();
        $this->put(route('portal.branch.update'), ['branch' => PortalSession::ALL_BRANCHES])->assertForbidden();
        $this->assertSame($jeddah->id, session(PortalSession::BRANCH_KEY));
    }

    public function test_branch_user_is_blocked_when_their_branch_is_inactive(): void
    {
        [$client] = $this->activeClient();
        $closed = ClientBranch::factory()->for($client)->inactive()->create();
        $user = ClientUser::factory()->forBranch($closed)->create();

        $this->assertSame(PortalBlock::NoBranch, $user->portalBlock());
    }

    public function test_sidebar_locks_modules_outside_the_plan(): void
    {
        [, , $owner] = $this->activeClient();

        $this->actingAs($owner, 'client')->get(route('portal.home'))
            ->assertOk()
            ->assertSee(__('portal.nav.locked'));
    }

    public function test_guests_are_sent_to_portal_login(): void
    {
        $this->get(route('portal.home'))->assertRedirect(route('portal.login'));
        $this->get('/')->assertRedirect('/portal');
    }

    public function test_portal_logout(): void
    {
        [, , $owner] = $this->activeClient();

        $this->actingAs($owner, 'client')
            ->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('client');
    }
}
