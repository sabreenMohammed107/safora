<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ClientPermission;
use App\Models\ClientUser;

/**
 * Company managers (and employees with users.manage) managing their own company's accounts.
 * Only an owner may create or change another owner.
 */
class ClientUserPolicy
{
    public function viewAny(ClientUser $actor): bool
    {
        return $actor->hasPermission(ClientPermission::UsersManage);
    }

    public function create(ClientUser $actor): bool
    {
        return $actor->hasPermission(ClientPermission::UsersManage);
    }

    public function update(ClientUser $actor, ClientUser $member): bool
    {
        return $actor->hasPermission(ClientPermission::UsersManage)
            && $member->client_id === $actor->client_id
            && (! $member->isOwner() || $actor->isOwner());
    }

    public function appointOwner(ClientUser $actor): bool
    {
        return $actor->isOwner();
    }
}
