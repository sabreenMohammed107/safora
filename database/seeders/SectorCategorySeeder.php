<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Sector;
use App\Models\SectorCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the 7 client categories and the sectors each one grants
 * (seedData().categories in docs/html-assets/11_ClientCenter.html). Requires SectorSeeder.
 */
class SectorCategorySeeder extends Seeder
{
    /** @var list<array{code: string, ar: string, en: string, icon: string, active: bool, sectors: list<string>}> */
    public const array CATEGORIES = [
        ['code' => 'CAT-01', 'ar' => 'مستشفيات', 'en' => 'Hospitals', 'icon' => 'fa-hospital', 'active' => true,
            'sectors' => ['RTE/Catering', 'Infant/Dietetic', 'Dairy', 'Meat/Poultry', 'Fruits']],
        ['code' => 'CAT-02', 'ar' => 'مطاعم', 'en' => 'Restaurants', 'icon' => 'fa-utensils', 'active' => true,
            'sectors' => ['RTE/Catering', 'Frozen/RTE', 'Meat/Poultry', 'Sauces/Condiments']],
        ['code' => 'CAT-03', 'ar' => 'فنادق', 'en' => 'Hotels', 'icon' => 'fa-hotel', 'active' => true,
            'sectors' => ['RTE/Catering', 'Dairy', 'Beverages', 'Bakery/Pasta', 'Fruits']],
        ['code' => 'CAT-04', 'ar' => 'مصانع أغذية', 'en' => 'Food Factories', 'icon' => 'fa-industry', 'active' => true,
            'sectors' => ['Dairy', 'Canned/Low-acid', 'Canned/Acidified', 'Frozen/RTE', 'Cereals']],
        ['code' => 'CAT-05', 'ar' => 'شركات التموين', 'en' => 'Catering', 'icon' => 'fa-truck-ramp-box', 'active' => true,
            'sectors' => ['RTE/Catering', 'Frozen/RTE', 'Meat/Poultry', 'Bakery/Pasta']],
        ['code' => 'CAT-06', 'ar' => 'مخابز وحلويات', 'en' => 'Bakeries & Sweets', 'icon' => 'fa-bread-slice', 'active' => true,
            'sectors' => ['Cereals', 'Bakery/Pasta', 'Chocolate/Sweets']],
        ['code' => 'CAT-07', 'ar' => 'منافذ تجزئة غذائية', 'en' => 'Food Retail', 'icon' => 'fa-store', 'active' => false,
            'sectors' => ['General Food']],
    ];

    public function run(): void
    {
        $sectorIds = Sector::pluck('id', 'key');

        foreach (self::CATEGORIES as $data) {
            $category = SectorCategory::updateOrCreate(['code' => $data['code']], [
                'name' => ['ar' => $data['ar'], 'en' => $data['en']],
                'icon' => $data['icon'],
                'active' => $data['active'],
            ]);

            $category->sectors()->sync($sectorIds->only($data['sectors'])->values());
        }
    }
}
