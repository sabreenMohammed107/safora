<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Permissions a SAFORA staff role can grant. Each value is also a Gate ability,
 * so routes use `can:clients.approve` and views use `@can('clients.approve')`.
 * Super admins hold every permission.
 */
enum AdminPermission: string
{
    case ClientsView = 'clients.view';
    case ClientsApprove = 'clients.approve';
    case ClientsSuspend = 'clients.suspend';
    case ClientsImpersonate = 'clients.impersonate';
    case ClientsDocuments = 'clients.documents';

    case PlansManage = 'plans.manage';
    case CategoriesManage = 'categories.manage';

    case HaccpManage = 'haccp.manage';
    case IsoManage = 'iso.manage';

    case StaffManage = 'staff.manage';
    case RolesManage = 'roles.manage';

    public function group(): string
    {
        return match ($this) {
            self::ClientsView, self::ClientsApprove, self::ClientsSuspend, self::ClientsImpersonate, self::ClientsDocuments => 'clients',
            self::PlansManage, self::CategoriesManage => 'catalog',
            self::HaccpManage, self::IsoManage => 'content',
            self::StaffManage, self::RolesManage => 'administration',
        };
    }

    public function label(): string
    {
        return __('permissions.admin.'.$this->value);
    }

    /**
     * Permissions keyed by group, in declaration order.
     *
     * @return array<string, list<self>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::cases() as $permission) {
            $groups[$permission->group()][] = $permission;
        }

        return $groups;
    }
}
