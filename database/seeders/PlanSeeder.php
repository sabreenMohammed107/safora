<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlanModule;
use App\Enums\PlanTier;
use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the only two plans (docs/sprints/03_BUSINESS_RULES.md §1).
 * ISO always includes HACCP, so it references the HACCP plan as its included plan.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $haccp = Plan::updateOrCreate(['code' => Plan::HACCP], [
            'name' => ['ar' => 'خطة الهاسب', 'en' => 'HACCP'],
            'tier' => PlanTier::Pro,
            'price_monthly' => 100,
            'price_annual' => 1200,
            'included_branches' => 1,
            'extra_branch_monthly' => 50,
            'extra_branch_annual' => 600,
            'active' => true,
        ]);

        $iso = Plan::updateOrCreate(['code' => Plan::ISO], [
            'name' => ['ar' => 'خطة ISO 22000', 'en' => 'ISO 22000'],
            'tier' => PlanTier::Enterprise,
            'price_monthly' => 200,
            'price_annual' => 2400,
            'included_branches' => 1,
            'extra_branch_monthly' => 100,
            'extra_branch_annual' => 1200,
            'includes_plan_id' => $haccp->id,
            'active' => true,
        ]);

        $this->syncModules($haccp, PlanModule::haccp());
        $this->syncModules($iso, PlanModule::cases());
    }

    /**
     * @param  list<PlanModule>  $modules
     */
    private function syncModules(Plan $plan, array $modules): void
    {
        foreach ($modules as $module) {
            $plan->modules()->firstOrCreate(['module_key' => $module]);
        }
    }
}
