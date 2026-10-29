<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sector;
use App\Models\SectorCategory;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SectorCategorySeeder;
use Database\Seeders\SectorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceDataSeederTest extends TestCase
{
    use RefreshDatabase;

    private const array REFERENCE_SEEDERS = [
        PlanSeeder::class,
        SectorSeeder::class,
        SectorCategorySeeder::class,
        ProductSeeder::class,
    ];

    public function test_seeds_categories_sectors_and_products_idempotently(): void
    {
        $this->seed(self::REFERENCE_SEEDERS);
        $this->seed(self::REFERENCE_SEEDERS);

        $this->assertSame(7, SectorCategory::count());
        $this->assertSame(28, Sector::count());
        $this->assertSame(546, Product::count());

        $product = Product::where('code', 'PRD-0001')->with('sector')->firstOrFail();
        $this->assertSame('حليب خام', $product->getTranslation('name', 'ar'));
        $this->assertSame('Raw milk', $product->getTranslation('name', 'en'));
        $this->assertSame('Dairy', $product->sector->key);

        $hospitals = SectorCategory::where('code', 'CAT-01')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            ['RTE/Catering', 'Infant/Dietetic', 'Dairy', 'Meat/Poultry', 'Fruits'],
            $hospitals->sectors->pluck('key')->all(),
        );
        $this->assertFalse(SectorCategory::where('code', 'CAT-07')->value('active'));
    }

    public function test_client_allowed_sectors_follow_seeded_category(): void
    {
        $this->seed(self::REFERENCE_SEEDERS);
        $bakeries = SectorCategory::where('code', 'CAT-06')->firstOrFail();

        $client = Client::factory()->for($bakeries, 'category')->create();

        $this->assertEqualsCanonicalizing(
            ['Cereals', 'Bakery/Pasta', 'Chocolate/Sweets'],
            $client->allowedSectors()->pluck('key')->all(),
        );
    }
}
