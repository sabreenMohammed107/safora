<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientDocument;
use App\Models\ClientUser;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\ClientRegistrationService;
use App\Services\PortalSession;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminClientManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->admin = Admin::factory()->create();
    }

    public function test_dashboard_shows_counts_and_pending_requests(): void
    {
        Client::factory()->create(['company' => 'Pending Hotel']);
        Client::factory()->active()->create();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pending Hotel')
            ->assertSee(Plan::where('code', Plan::ISO)->first()->name);
    }

    public function test_client_list_filters_by_status_and_search(): void
    {
        Client::factory()->create(['company' => 'Waha Hotel']);
        Client::factory()->active()->create(['company' => 'Nakha Restaurants']);

        $admin = $this->actingAs($this->admin, 'admin');

        $admin->get(route('admin.clients.index'))->assertOk()->assertSee('Waha Hotel')->assertSee('Nakha Restaurants');
        $admin->get(route('admin.clients.index', ['status' => 'pending']))->assertSee('Waha Hotel')->assertDontSee('Nakha Restaurants');
        $admin->get(route('admin.clients.index', ['q' => 'Nakha']))->assertSee('Nakha Restaurants')->assertDontSee('Waha Hotel');
    }

    public function test_client_list_shows_price_with_extra_branches(): void
    {
        $client = Client::factory()->active()->create();
        ClientBranch::factory()->for($client)->count(3)->create();
        Subscription::factory()->for($client)->create(['plan_id' => Plan::where('code', Plan::HACCP)->value('id')]);

        // 100 + 2 × 50 = 200, + 15% VAT = 230.00
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients.index'))
            ->assertSee('230.00');
    }

    public function test_profile_renders(): void
    {
        $client = Client::factory()->active()->create();
        ClientUser::factory()->for($client)->create(['name' => 'Sara Owner']);
        Subscription::factory()->for($client)->create(['plan_id' => Plan::first()->id]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee($client->company)
            ->assertSee('Sara Owner');
    }

    public function test_admin_approves_pending_client(): void
    {
        $client = Client::factory()->create();
        $plan = Plan::where('code', Plan::ISO)->first();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.approve', $client), [
                'plan_id' => $plan->id,
                'cycle' => 'annual',
                'start_date' => '2026-11-01',
                'discount' => 5,
            ])
            ->assertRedirect(route('admin.clients.show', $client))
            ->assertSessionHas('status');

        $client->refresh();
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertTrue($client->subscription->plan->is($plan));
        $this->assertSame('2027-11-01', $client->subscription->end_date->toDateString());

        $log = $client->activityLogs()->first();
        $this->assertTrue($log->actor->is($this->admin));
        $this->assertSame($this->admin->name, $log->actor_name);
    }

    public function test_approval_validates_input(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.approve', $client), ['plan_id' => 999, 'cycle' => 'weekly', 'discount' => 150])
            ->assertSessionHasErrors(['plan_id', 'cycle', 'start_date', 'discount']);

        $this->assertSame(ClientStatus::Pending, $client->fresh()->status);
    }

    public function test_reject_requires_reason(): void
    {
        $client = Client::factory()->create();
        $admin = $this->actingAs($this->admin, 'admin');

        $admin->post(route('admin.clients.reject', $client))->assertSessionHasErrors('reason');

        $admin->post(route('admin.clients.reject', $client), ['reason' => 'CR mismatch'])
            ->assertRedirect(route('admin.clients.show', $client));

        $this->assertSame(ClientStatus::Rejected, $client->fresh()->status);
    }

    public function test_suspend_and_reactivate(): void
    {
        $client = Client::factory()->active()->create();
        $admin = $this->actingAs($this->admin, 'admin');

        $admin->post(route('admin.clients.suspend', $client), ['reason' => 'Overdue invoice']);
        $this->assertSame(ClientStatus::Suspended, $client->fresh()->status);

        $admin->post(route('admin.clients.reactivate', $client));
        $this->assertSame(ClientStatus::Active, $client->fresh()->status);
    }

    public function test_invalid_transition_shows_error(): void
    {
        $client = Client::factory()->rejected()->create();

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.clients.show', $client))
            ->post(route('admin.clients.suspend', $client), ['reason' => 'x'])
            ->assertRedirect(route('admin.clients.show', $client))
            ->assertSessionHas('error');
    }

    public function test_document_download_is_scoped_to_its_client(): void
    {
        Storage::fake(ClientRegistrationService::DOCUMENT_DISK);
        Storage::disk(ClientRegistrationService::DOCUMENT_DISK)->put('clients/1/documents/cr.pdf', 'pdf');

        $client = Client::factory()->create();
        $other = Client::factory()->create();
        $document = $client->documents()->create([
            'type' => ClientDocument::TYPE_REGISTRATION,
            'original_name' => 'cr.pdf',
            'file_path' => 'clients/1/documents/cr.pdf',
            'mime_type' => 'application/pdf',
            'size' => 3,
        ]);

        $admin = $this->actingAs($this->admin, 'admin');

        $admin->get(route('admin.clients.documents.show', [$client, $document]))->assertOk()->assertDownload('cr.pdf');
        $admin->get(route('admin.clients.documents.show', [$other, $document]))->assertNotFound();
    }

    public function test_impersonation_opens_portal_as_owner(): void
    {
        $client = Client::factory()->active()->create();
        $owner = ClientUser::factory()->for($client)->create();
        ClientBranch::factory()->for($client)->create();
        Subscription::factory()->for($client)->create(['plan_id' => Plan::first()->id]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.impersonate', $client))
            ->assertRedirect(route('portal.home'));

        $this->assertAuthenticatedAs($owner, 'client');
        $this->assertSame($this->admin->id, session(PortalSession::IMPERSONATOR_KEY));

        $this->get(route('portal.home'))->assertOk()->assertSee(__('portal.impersonation.banner'));

        $this->post(route('portal.logout'))->assertRedirect(route('admin.clients.show', $client));
        $this->assertGuest('client');
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }
}
