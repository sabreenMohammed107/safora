<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

/**
 * Seeds the 28 food sectors (SECTOR_AR in docs/html-assets/11_ClientCenter.html).
 * Keys match the prototype so imported localStorage data maps 1:1.
 * English names still need review by a food-safety specialist (docs/sprints/06_I18N.md §5).
 */
class SectorSeeder extends Seeder
{
    /** @var array<string, array{ar: string, en: string}> */
    public const array SECTORS = [
        'Dairy' => ['ar' => 'الألبان ومنتجاتها', 'en' => 'Dairy & Dairy Products'],
        'RTE/Catering' => ['ar' => 'الأغذية الجاهزة والتموين', 'en' => 'Ready-to-Eat & Catering'],
        'Frozen/RTE' => ['ar' => 'الأغذية المجمدة', 'en' => 'Frozen Foods'],
        'Canned/Acidified' => ['ar' => 'المعلبات الحمضية والمحمّضة', 'en' => 'Acid & Acidified Canned Foods'],
        'Canned/Low-acid' => ['ar' => 'المعلبات منخفضة الحموضة', 'en' => 'Low-Acid Canned Foods'],
        'Fish/Seafood' => ['ar' => 'الأسماك والمأكولات البحرية', 'en' => 'Fish & Seafood'],
        'Nuts/Seeds' => ['ar' => 'المكسرات والبذور', 'en' => 'Nuts & Seeds'],
        'Dried Fruit' => ['ar' => 'الفواكه المجففة', 'en' => 'Dried Fruits'],
        'Spices/Herbs' => ['ar' => 'التوابل والأعشاب', 'en' => 'Spices & Herbs'],
        'Cereals' => ['ar' => 'الحبوب ومنتجاتها', 'en' => 'Cereals & Cereal Products'],
        'Infant/Dietetic' => ['ar' => 'أغذية الرضع والحمية', 'en' => 'Infant & Dietetic Foods'],
        'Beverages' => ['ar' => 'المشروبات', 'en' => 'Beverages'],
        'Fats/Oils' => ['ar' => 'الزيوت والدهون', 'en' => 'Oils & Fats'],
        'Water/Ice' => ['ar' => 'المياه والثلج', 'en' => 'Water & Ice'],
        'Ingredients' => ['ar' => 'المكونات', 'en' => 'Food Ingredients'],
        'Vegetables' => ['ar' => 'الخضروات', 'en' => 'Vegetables'],
        'Legumes' => ['ar' => 'البقوليات', 'en' => 'Legumes'],
        'Chocolate/Sweets' => ['ar' => 'الشوكولاتة والحلويات', 'en' => 'Chocolate & Confectionery'],
        'Sugars/Honey' => ['ar' => 'السكر والعسل', 'en' => 'Sugar & Honey'],
        'General Food' => ['ar' => 'أغذية عامة', 'en' => 'General Food'],
        'Meat/Poultry' => ['ar' => 'اللحوم والدواجن', 'en' => 'Meat & Poultry'],
        'Eggs' => ['ar' => 'البيض', 'en' => 'Eggs'],
        'Bakery/Pasta' => ['ar' => 'المخبوزات والمعكرونة', 'en' => 'Bakery & Pasta'],
        'Fruits' => ['ar' => 'الفواكه', 'en' => 'Fruits'],
        'Sauces/Condiments' => ['ar' => 'الصلصات والمخللات', 'en' => 'Sauces & Pickles'],
        'Fermented' => ['ar' => 'الأغذية المتخمرة', 'en' => 'Fermented Foods'],
        'Traditional' => ['ar' => 'الأغذية التقليدية', 'en' => 'Traditional Foods'],
        'Plant Protein' => ['ar' => 'البروتين النباتي', 'en' => 'Plant Protein'],
    ];

    public function run(): void
    {
        foreach (self::SECTORS as $key => $name) {
            Sector::updateOrCreate(['key' => $key], ['name' => $name, 'active' => true]);
        }
    }
}
