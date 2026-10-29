<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanTier: string
{
    case Basic = 'basic';
    case Pro = 'pro';
    case Enterprise = 'enterprise';
}
