<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Account type of a portal user. What a member may do comes from their client role
 * (ClientRole permissions); the owner implicitly holds every permission their plan allows.
 */
enum ClientUserRole: string
{
    /** Company manager: every branch, the "all branches" view and full permissions. */
    case Owner = 'owner';

    /** Employee: one branch, permissions from their assigned client role. */
    case Member = 'member';

    public function label(): string
    {
        return __('portal.roles.'.$this->value);
    }
}
