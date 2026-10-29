<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PriceQuote;
use App\Services\PricingService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->pricing = new PricingService(vatRate: 0.15, currency: 'SAR');
    }

    /**
     * @return array<string, array{string, BillingCycle, int, float, string, string, string, string}>
     */
    public static function quotes(): array
    {
        // plan, cycle, branches, discount % => subtotal, discount, vat, total
        return [
            'haccp monthly, included branch only' => [Plan::HACCP, BillingCycle::Monthly, 1, 0, '100.00', '0.00', '15.00', '115.00'],
            'haccp monthly, 2 extra branches, 10% off' => [Plan::HACCP, BillingCycle::Monthly, 3, 10, '200.00', '20.00', '27.00', '207.00'],
            'haccp annual, 1 extra branch' => [Plan::HACCP, BillingCycle::Annual, 2, 0, '1800.00', '0.00', '270.00', '2070.00'],
            'iso monthly, 1 extra branch' => [Plan::ISO, BillingCycle::Monthly, 2, 0, '300.00', '0.00', '45.00', '345.00'],
            'iso annual, 3 extra branches, 25% off' => [Plan::ISO, BillingCycle::Annual, 4, 25, '6000.00', '1500.00', '675.00', '5175.00'],
            'no active branches never goes below plan price' => [Plan::HACCP, BillingCycle::Monthly, 0, 0, '100.00', '0.00', '15.00', '115.00'],
            'full discount' => [Plan::ISO, BillingCycle::Monthly, 1, 100, '200.00', '200.00', '0.00', '0.00'],
            'fractional discount rounds to the halala' => [Plan::HACCP, BillingCycle::Monthly, 1, 33.33, '100.00', '33.33', '10.00', '76.67'],
        ];
    }

    #[DataProvider('quotes')]
    public function test_quote(string $planCode, BillingCycle $cycle, int $branches, float $discount, string $subtotal, string $discountAmount, string $vat, string $total): void
    {
        $quote = $this->pricing->quote(Plan::where('code', $planCode)->firstOrFail(), $cycle, $branches, $discount)->toArray();

        $this->assertSame($subtotal, $quote['subtotal']);
        $this->assertSame($discountAmount, $quote['discount_amount']);
        $this->assertSame($vat, $quote['vat_amount']);
        $this->assertSame($total, $quote['total']);
    }

    public function test_quote_breakdown_lists_extra_branches(): void
    {
        $quote = $this->pricing->quote(Plan::where('code', Plan::HACCP)->firstOrFail(), BillingCycle::Monthly, 3);

        $this->assertSame(2, $quote->extraBranches);
        $this->assertSame(5000, $quote->extraBranchPrice);
        $this->assertSame(10000, $quote->extraBranchesTotal);
        $this->assertSame('SAR', $quote->currency);
    }

    public function test_quote_subscription_uses_its_plan_cycle_and_discount(): void
    {
        $subscription = Subscription::factory()->annual()->create([
            'plan_id' => Plan::where('code', Plan::ISO)->value('id'),
            'discount' => 10,
        ]);

        $quote = $this->pricing->quoteSubscription($subscription, activeBranches: 2);

        $this->assertSame('3240.00', PriceQuote::toDecimal($quote->netTotal));
    }

    public function test_rejects_out_of_range_discount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pricing->quote(Plan::firstOrFail(), BillingCycle::Monthly, 1, 120);
    }

    public function test_container_resolves_service_with_configured_vat(): void
    {
        config(['safora.vat_rate' => 0.05]);
        $this->app->forgetInstance(PricingService::class);

        $quote = app(PricingService::class)->quote(Plan::where('code', Plan::HACCP)->firstOrFail(), BillingCycle::Monthly, 1);

        $this->assertSame(500, $quote->vatAmount);
    }
}
