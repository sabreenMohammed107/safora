<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AdminRole;

class AdminRolePolicy
{
    public function viewAny(Admin $actor): bool
    {
        return $actor->hasPermission(AdminPermission::RolesManage);
    }

    public function create(Admin $actor): bool
    {
        return $actor->hasPermission(AdminPermission::RolesManage);
    }

    /**
     * Staff who are not super admins may only edit roles whose permissions they hold themselves,
     * so nobody can widen a role beyond their own access.
     */
    public function update(Admin $actor, AdminRole $role): bool
    {
        return $actor->hasPermission(AdminPermission::RolesManage)
            && $role->permissions->every(fn (AdminPermission $permission): bool => $actor->hasPermission($permission));
    }

    public function delete(Admin $actor, AdminRole $role): bool
    {
        return $this->update($actor, $role) && ! $role->admins()->exists();
    }
}
