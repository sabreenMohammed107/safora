<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Annual = 'annual';

    /**
     * Subscription end date for a term starting on the given date.
     */
    public function endDateFrom(CarbonInterface $start): CarbonImmutable
    {
        $start = CarbonImmutable::instance($start)->startOfDay();

        return match ($this) {
            self::Monthly => $start->addMonthNoOverflow(),
            self::Annual => $start->addYearNoOverflow(),
        };
    }
}
