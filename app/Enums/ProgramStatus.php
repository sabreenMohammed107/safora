<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A HACCP program's publication state. Draft programs are hidden from clients.
 */
enum ProgramStatus: string
{
    case Active = 'active';
    case Draft = 'draft';

    public function label(): string
    {
        return __('haccp.status.'.$this->value);
    }
}
