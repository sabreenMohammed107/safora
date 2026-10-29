<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillingCycle;
use App\Models\Plan;
use App\Models\Subscription;
use InvalidArgumentException;

/**
 * Price = plan price + (active branches − included branches) × extra-branch price − discount % (+ VAT).
 *
 * @see docs/sprints/03_BUSINESS_RULES.md §1
 */
class PricingService
{
    public function __construct(
        private readonly float $vatRate,
        private readonly string $currency,
    ) {}

    public function quote(Plan $plan, BillingCycle $cycle, int $activeBranches, float $discountPercent = 0): PriceQuote
    {
        if ($activeBranches < 0) {
            throw new InvalidArgumentException('Active branches cannot be negative.');
        }

        if ($discountPercent < 0 || $discountPercent > 100) {
            throw new InvalidArgumentException('Discount must be between 0 and 100 percent.');
        }

        [$basePrice, $extraBranchPrice] = match ($cycle) {
            BillingCycle::Monthly => [$plan->price_monthly, $plan->extra_branch_monthly],
            BillingCycle::Annual => [$plan->price_annual, $plan->extra_branch_annual],
        };

        $basePrice = $this->toHalalas($basePrice);
        $extraBranchPrice = $this->toHalalas($extraBranchPrice);

        $extraBranches = max(0, $activeBranches - $plan->included_branches);
        $extraBranchesTotal = $extraBranches * $extraBranchPrice;
        $subtotal = $basePrice + $extraBranchesTotal;
        $discountAmount = (int) round($subtotal * $discountPercent / 100);
        $netTotal = $subtotal - $discountAmount;
        $vatAmount = (int) round($netTotal * $this->vatRate);

        return new PriceQuote(
            cycle: $cycle,
            basePrice: $basePrice,
            activeBranches: $activeBranches,
            includedBranches: $plan->included_branches,
            extraBranches: $extraBranches,
            extraBranchPrice: $extraBranchPrice,
            extraBranchesTotal: $extraBranchesTotal,
            subtotal: $subtotal,
            discountPercent: $discountPercent,
            discountAmount: $discountAmount,
            netTotal: $netTotal,
            vatRate: $this->vatRate,
            vatAmount: $vatAmount,
            total: $netTotal + $vatAmount,
            currency: $this->currency,
        );
    }

    public function quoteSubscription(Subscription $subscription, int $activeBranches): PriceQuote
    {
        return $this->quote(
            $subscription->plan,
            $subscription->cycle,
            $activeBranches,
            (float) $subscription->discount,
        );
    }

    private function toHalalas(string|int|float|null $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
