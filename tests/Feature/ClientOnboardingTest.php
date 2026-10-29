<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\ClientStatus;
use App\Enums\ClientUserRole;
use App\Exceptions\InvalidClientStatusTransition;
use App\Http\Requests\RegisterClientRequest;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Plan;
use App\Models\SectorCategory;
use App\Rules\SaudiCrNumber;
use App\Services\ClientApprovalService;
use App\Services\ClientRegistrationService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_pending_client_with_documents_and_log(): void
    {
        Storage::fake(ClientRegistrationService::DOCUMENT_DISK);
        $category = SectorCategory::factory()->create();

        $client = app(ClientRegistrationService::class)->register([
            'company' => 'مستشفى الرعاية التخصصي',
            'sector_category_id' => $category->id,
            'contact_name' => 'Sara',
            'phone' => '+966 50 123 4567',
            'email' => 'ops@example.com',
            'cr_number' => '1010456789',
        ], 'Secret123', [UploadedFile::fake()->create('cr.pdf', 200, 'application/pdf')]);

        $this->assertSame(ClientStatus::Pending, $client->status);
        $this->assertSame('CL-'.(1000 + $client->id), $client->code);
        $this->assertNull($client->subscription);

        $branch = $client->branches()->sole();
        $this->assertSame('BR-01', $branch->code);
        $this->assertTrue($branch->active);

        $owner = $client->owner;
        $this->assertSame('ops@example.com', $owner->email);
        $this->assertSame(ClientUserRole::Owner, $owner->role);
        $this->assertNull($owner->branch_id);
        $this->assertTrue(Hash::check('Secret123', $owner->password));

        $document = $client->documents()->sole();
        $this->assertSame('cr.pdf', $document->original_name);
        Storage::disk(ClientRegistrationService::DOCUMENT_DISK)->assertExists($document->file_path);

        $log = $client->activityLogs()->sole();
        $this->assertSame('registered', $log->event);
        $this->assertSame('Sara', $log->actor_name);
    }

    public function test_approval_creates_subscription_and_activates_client(): void
    {
        $this->seed(PlanSeeder::class);
        Carbon::setTestNow('2026-01-31 10:00');
        $client = Client::factory()->create();
        $plan = Plan::where('code', Plan::HACCP)->firstOrFail();

        $subscription = app(ClientApprovalService::class)->approve($client, $plan, BillingCycle::Monthly, discountPercent: 10);

        $client->refresh();
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertNotNull($client->approved_at);
        $this->assertTrue($client->subscription->is($subscription));
        $this->assertSame('2026-01-31', $subscription->start_date->toDateString());
        $this->assertSame('2026-02-28', $subscription->end_date->toDateString());
        $this->assertSame('10.00', $subscription->discount);
        $this->assertSame(
            'Account approved and subscribed to HACCP (Monthly)',
            $client->activityLogs()->sole()->description('en'),
        );
    }

    public function test_annual_approval_runs_one_year(): void
    {
        $this->seed(PlanSeeder::class);
        $client = Client::factory()->create();

        $subscription = app(ClientApprovalService::class)->approve(
            $client,
            Plan::where('code', Plan::ISO)->firstOrFail(),
            BillingCycle::Annual,
            Carbon::parse('2026-03-01'),
        );

        $this->assertSame('2027-03-01', $subscription->end_date->toDateString());
    }

    public function test_rejection_stores_reason(): void
    {
        $client = Client::factory()->create();

        app(ClientApprovalService::class)->reject($client, 'CR number mismatch');

        $client->refresh();
        $this->assertSame(ClientStatus::Rejected, $client->status);
        $this->assertSame('CR number mismatch', $client->status_reason);
        $this->assertSame('رفض الطلب: CR number mismatch', $client->activityLogs()->sole()->description('ar'));
    }

    public function test_suspend_and_reactivate(): void
    {
        $client = Client::factory()->active()->create();
        $service = app(ClientApprovalService::class);

        $service->suspend($client, 'Overdue invoice');
        $this->assertSame(ClientStatus::Suspended, $client->fresh()->status);

        $service->reactivate($client);
        $client->refresh();
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertNull($client->status_reason);
        $this->assertSame(2, $client->activityLogs()->count());
    }

    public function test_cannot_approve_rejected_client(): void
    {
        $this->seed(PlanSeeder::class);
        $client = Client::factory()->rejected()->create();

        $this->expectException(InvalidClientStatusTransition::class);

        app(ClientApprovalService::class)->approve($client, Plan::firstOrFail(), BillingCycle::Monthly);
    }

    public function test_cannot_approve_twice_from_stale_instance(): void
    {
        $this->seed(PlanSeeder::class);
        $client = Client::factory()->create();
        $stale = Client::findOrFail($client->id);
        $service = app(ClientApprovalService::class);

        $service->approve($client, Plan::firstOrFail(), BillingCycle::Monthly);

        try {
            $service->approve($stale, Plan::firstOrFail(), BillingCycle::Monthly);
            $this->fail('Second approval should have been rejected.');
        } catch (InvalidClientStatusTransition) {
            $this->assertSame(1, $client->subscriptions()->count());
        }
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function crNumbers(): array
    {
        return [
            '10-digit CR' => ['1010456789', true],
            '15-digit VAT starting and ending with 3' => ['300123456700003', true],
            '15 digits not ending with 3' => ['300123456700004', false],
            '9 digits' => ['101045678', false],
            'letters' => ['10104567AB', false],
        ];
    }

    #[DataProvider('crNumbers')]
    public function test_cr_number_rule(string $value, bool $valid): void
    {
        $validator = Validator::make(['cr_number' => $value], ['cr_number' => [new SaudiCrNumber]]);

        $this->assertSame($valid, $validator->passes());
    }

    public function test_registration_request_rules(): void
    {
        $active = SectorCategory::factory()->create();
        $inactive = SectorCategory::factory()->create(['active' => false]);
        ClientUser::factory()->create(['email' => 'taken@example.com']);

        $rules = (new RegisterClientRequest)->rules();
        $valid = [
            'company' => 'Co',
            'sector_category_id' => $active->id,
            'contact_name' => 'Name',
            'phone' => '0500000000',
            'email' => 'new@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'cr_number' => '1010456789',
            'documents' => [UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf')],
        ];

        $this->assertTrue(Validator::make($valid, $rules)->passes());

        $errors = Validator::make([
            ...$valid,
            'sector_category_id' => $inactive->id,
            'email' => 'taken@example.com',
            'password' => 'short',
            'documents' => [UploadedFile::fake()->create('cr.exe', 100)],
        ], $rules)->errors();

        $this->assertTrue($errors->has('sector_category_id'));
        $this->assertTrue($errors->has('email'));
        $this->assertTrue($errors->has('password'));
        $this->assertTrue($errors->has('documents.0'));
    }
}
