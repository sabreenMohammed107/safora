<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanModule: string
{
    case Prp = 'prp';
    case Haccp = 'haccp';
    case Ccp = 'ccp';
    case Limits = 'limits';
    case Trace = 'trace';
    case Sops = 'sops';
    case Monthly = 'monthly';
    case Forms = 'forms';
    case Incidents = 'incidents';
    case Iso = 'iso';

    /**
     * Modules bundled with the HACCP plan.
     *
     * @return list<self>
     */
    public static function haccp(): array
    {
        return [self::Prp, self::Haccp, self::Ccp, self::Limits, self::Trace];
    }
}
