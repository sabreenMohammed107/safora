<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\SectorSource;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\Product;
use App\Models\Sector;
use App\Models\SectorCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSectorsTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Sector $inherited;

    private Sector $legumes;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $category = SectorCategory::factory()->create();
        $this->inherited = Sector::factory()->create(['name' => ['ar' => 'الألبان', 'en' => 'Dairy']]);
        $category->sectors()->attach($this->inherited);
        Product::factory()->for($this->inherited)->create();

        $this->legumes = Sector::factory()->create(['name' => ['ar' => 'البقوليات', 'en' => 'Legumes']]);
        Product::factory()->for($this->legumes)->count(7)->create();

        Sector::factory()->create(['name' => ['ar' => 'بلا منتجات', 'en' => 'Empty']]);

        $this->client = Client::factory()->active()->for($category, 'category')->create();
        $this->admin = Admin::factory()->create();
    }

    public function test_profile_offers_only_sectors_not_allowed_and_with_products(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.clients.show', $this->client))
            ->assertOk()
            ->assertSee('البقوليات (7 منتج)')
            ->assertDontSee('بلا منتجات')
            ->assertSee(__('admin.clients.sectors.legend.added'));
    }

    public function test_admin_grants_sector(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.clients.sectors.store', $this->client), ['sector_id' => $this->legumes->id])
            ->assertSessionHas('status');

        $this->assertSame(SectorSource::Granted, $this->client->extraSectors()->sole()->pivot->source);
        $this->assertTrue($this->client->allowedSectors()->whereKey($this->legumes->id)->exists());
        $this->assertSame('sector_granted', $this->client->activityLogs()->first()->event);

        // Granting an already allowed (inherited) sector changes nothing.
        $this->post(route('admin.clients.sectors.store', $this->client), ['sector_id' => $this->inherited->id]);
        $this->assertSame(1, $this->client->extraSectors()->count());
    }

    public function test_revoking_removes_that_sectors_products_from_branches(): void
    {
        $this->client->extraSectors()->attach($this->legumes, ['source' => SectorSource::Added]);
        $branch = ClientBranch::factory()->for($this->client)->create();
        $branch->products()->attach([...$this->legumes->products()->limit(2)->pluck('id'), $this->inherited->products()->value('id')]);

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.clients.sectors.destroy', [$this->client, $this->legumes]))
            ->assertSessionHas('status');

        $this->assertSame(0, $this->client->extraSectors()->count());
        $this->assertSame([$this->inherited->products()->value('id')], $branch->products()->pluck('products.id')->all());

        $log = $this->client->activityLogs()->first();
        $this->assertSame('sector_revoked_with_products', $log->event);
        $this->assertSame(2, $log->properties['count']);
    }

    public function test_inherited_sectors_cannot_be_revoked(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.clients.sectors.destroy', [$this->client, $this->inherited]))
            ->assertNotFound();
    }

    public function test_requires_permission(): void
    {
        $viewer = Admin::factory()->withRole(AdminRole::factory()->granting([AdminPermission::ClientsView])->create())->create();

        $this->actingAs($viewer, 'admin');
        $this->get(route('admin.clients.show', $this->client))->assertOk()->assertDontSee(route('admin.clients.sectors.store', $this->client));
        $this->post(route('admin.clients.sectors.store', $this->client), ['sector_id' => $this->legumes->id])->assertForbidden();
    }
}
