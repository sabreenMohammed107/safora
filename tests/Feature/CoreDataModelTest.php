<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PlanModule;
use App\Enums\SectorSource;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Sector;
use App\Models\SectorCategory;
use App\Models\Subscription;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_seeder_creates_both_plans_with_iso_including_haccp(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(PlanSeeder::class);

        $this->assertSame(2, Plan::count());

        $haccp = Plan::where('code', Plan::HACCP)->firstOrFail();
        $iso = Plan::where('code', Plan::ISO)->with('includedPlan.modules', 'modules')->firstOrFail();

        $this->assertSame('100.00', $haccp->price_monthly);
        $this->assertSame('600.00', $haccp->extra_branch_annual);
        $this->assertSame('HACCP', $haccp->getTranslation('name', 'en'));
        $this->assertCount(5, $haccp->modules);
        $this->assertFalse($haccp->hasModule(PlanModule::Iso));

        $this->assertTrue($iso->includedPlan->is($haccp));
        $this->assertTrue($iso->hasModule(PlanModule::Iso));
        $this->assertTrue($iso->hasModule(PlanModule::Prp));
    }

    public function test_client_receives_sequential_code_on_creation(): void
    {
        $client = Client::factory()->create();

        $this->assertSame('CL-'.(1000 + $client->id), $client->fresh()->code);
    }

    public function test_client_relationships(): void
    {
        $category = SectorCategory::factory()->create();
        $inherited = Sector::factory()->create();
        $granted = Sector::factory()->create();
        $category->sectors()->attach($inherited);
        Product::factory()->for($inherited)->count(2)->create();

        $client = Client::factory()->for($category, 'category')->create();
        $client->extraSectors()->attach($granted, ['source' => SectorSource::Granted]);

        $old = Subscription::factory()->for($client)->expired()->create();
        $current = Subscription::factory()->for($client)->annual()->create();

        $client->logActivity('registered', actorName: 'Contact');

        $client->refresh();

        $this->assertTrue($client->category->is($category));
        $this->assertSame(SectorSource::Granted, $client->extraSectors->first()->pivot->source);
        $this->assertCount(2, $inherited->products);
        $this->assertTrue($client->subscription->is($current));
        $this->assertTrue($old->isExpired());
        $this->assertFalse($current->isExpired());
        $this->assertSame('Submitted a registration request', $client->activityLogs->first()->description('en'));
        $this->assertSame('تقديم طلب التسجيل', $client->activityLogs->first()->description('ar'));
    }

    public function test_allowed_sectors_resolve_live_from_category_plus_extras(): void
    {
        $category = SectorCategory::factory()->create();
        [$inherited, $granted, $later, $unrelated] = Sector::factory()->count(4)->create();
        $category->sectors()->attach($inherited);

        $client = Client::factory()->for($category, 'category')->create();
        $client->extraSectors()->attach($granted, ['source' => SectorSource::Granted]);
        Client::factory()->create()->extraSectors()->attach($unrelated, ['source' => SectorSource::Added]);

        $this->assertEqualsCanonicalizing([$inherited->id, $granted->id], $client->allowedSectors()->pluck('id')->all());

        $category->sectors()->attach($later);

        $this->assertEqualsCanonicalizing(
            [$inherited->id, $granted->id, $later->id],
            $client->allowedSectors()->pluck('id')->all(),
        );
    }
}
