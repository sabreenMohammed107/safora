<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ClientPermission;
use App\Models\ClientRole;
use App\Models\ClientUser;

/**
 * Company-level roles. A role is only ever visible to users of its own company.
 */
class ClientRolePolicy
{
    public function viewAny(ClientUser $actor): bool
    {
        return $actor->hasPermission(ClientPermission::RolesManage);
    }

    public function create(ClientUser $actor): bool
    {
        return $actor->hasPermission(ClientPermission::RolesManage);
    }

    /**
     * Non-owners may only edit roles whose (plan-available) permissions they hold themselves.
     */
    public function update(ClientUser $actor, ClientRole $role): bool
    {
        $plan = $actor->client->subscription?->plan;

        return $actor->hasPermission(ClientPermission::RolesManage)
            && $role->client_id === $actor->client_id
            && $role->permissions
                ->filter(fn (ClientPermission $permission): bool => $permission->isAvailableOn($plan))
                ->every(fn (ClientPermission $permission): bool => $actor->hasPermission($permission));
    }

    public function delete(ClientUser $actor, ClientRole $role): bool
    {
        return $this->update($actor, $role) && ! $role->users()->exists();
    }
}
