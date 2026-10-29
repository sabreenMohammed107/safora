<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Reasons the client portal is closed to a signed-in user (docs/sprints/02_CLIENT_PORTAL.md).
 */
enum PortalBlock: string
{
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case NoPlan = 'no_plan';
    case Expired = 'expired';
    case UserInactive = 'user_inactive';
    case NoBranch = 'no_branch';

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'fa-hourglass-half text-brand-deep',
            self::Rejected, self::Suspended, self::UserInactive => 'fa-ban text-rose',
            self::NoPlan => 'fa-layer-group text-ink/50',
            self::Expired => 'fa-calendar-xmark text-amber-deep',
            self::NoBranch => 'fa-store-slash text-ink/50',
        };
    }

    public function title(): string
    {
        return __('portal.blocked.'.$this->value.'.title');
    }

    public function message(): string
    {
        return __('portal.blocked.'.$this->value.'.text');
    }
}
