<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillingCycle;

/**
 * Immutable price breakdown. All amounts are integer halalas (1 SAR = 100) to avoid float drift.
 */
final readonly class PriceQuote
{
    public function __construct(
        public BillingCycle $cycle,
        public int $basePrice,
        public int $activeBranches,
        public int $includedBranches,
        public int $extraBranches,
        public int $extraBranchPrice,
        public int $extraBranchesTotal,
        public int $subtotal,
        public float $discountPercent,
        public int $discountAmount,
        public int $netTotal,
        public float $vatRate,
        public int $vatAmount,
        public int $total,
        public string $currency,
    ) {}

    public static function toDecimal(int $halalas): string
    {
        return number_format($halalas / 100, 2, '.', '');
    }

    /**
     * @return array{cycle: string, currency: string, base_price: string, active_branches: int, included_branches: int, extra_branches: int, extra_branch_price: string, extra_branches_total: string, subtotal: string, discount_percent: float, discount_amount: string, net_total: string, vat_rate: float, vat_amount: string, total: string}
     */
    public function toArray(): array
    {
        return [
            'cycle' => $this->cycle->value,
            'currency' => $this->currency,
            'base_price' => self::toDecimal($this->basePrice),
            'active_branches' => $this->activeBranches,
            'included_branches' => $this->includedBranches,
            'extra_branches' => $this->extraBranches,
            'extra_branch_price' => self::toDecimal($this->extraBranchPrice),
            'extra_branches_total' => self::toDecimal($this->extraBranchesTotal),
            'subtotal' => self::toDecimal($this->subtotal),
            'discount_percent' => $this->discountPercent,
            'discount_amount' => self::toDecimal($this->discountAmount),
            'net_total' => self::toDecimal($this->netTotal),
            'vat_rate' => $this->vatRate,
            'vat_amount' => self::toDecimal($this->vatAmount),
            'total' => self::toDecimal($this->total),
        ];
    }
}
