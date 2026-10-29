<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;

/**
 * Managing SAFORA staff accounts. Only a super admin may change another super admin.
 */
class AdminPolicy
{
    public function viewAny(Admin $actor): bool
    {
        return $actor->hasPermission(AdminPermission::StaffManage);
    }

    public function create(Admin $actor): bool
    {
        return $actor->hasPermission(AdminPermission::StaffManage);
    }

    public function update(Admin $actor, Admin $staff): bool
    {
        return $actor->hasPermission(AdminPermission::StaffManage)
            && (! $staff->is_super_admin || $actor->is_super_admin);
    }

    public function grantSuperAdmin(Admin $actor): bool
    {
        return $actor->is_super_admin && $actor->active;
    }
}
