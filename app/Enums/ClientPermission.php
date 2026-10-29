<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Plan;

/**
 * Permissions a company manager can grant to their employees through client roles.
 * Each value is also a Gate ability. A permission tied to a plan module is only effective
 * (and only grantable) while the client's plan includes that module.
 */
enum ClientPermission: string
{
    case RecordsView = 'records.view';
    case RecordsCreate = 'records.create';
    case RecordsEdit = 'records.edit';
    case RecordsDelete = 'records.delete';

    case PrpAccess = 'prp.access';
    case PrpCustomize = 'prp.customize';
    case HaccpPlansManage = 'haccp_plans.manage';
    case ControlPointsMonitor = 'control_points.monitor';
    case CriticalLimitsRecord = 'critical_limits.record';
    case TraceabilityManage = 'traceability.manage';
    case IsoAccess = 'iso.access';

    case ProductsManage = 'products.manage';
    case ProductRequestsCreate = 'product_requests.create';

    case BranchesManage = 'branches.manage';
    case UsersManage = 'users.manage';
    case RolesManage = 'roles.manage';
    case SubscriptionView = 'subscription.view';

    public function group(): string
    {
        return match ($this) {
            self::RecordsView, self::RecordsCreate, self::RecordsEdit, self::RecordsDelete => 'records',
            self::PrpAccess, self::PrpCustomize, self::HaccpPlansManage, self::ControlPointsMonitor,
            self::CriticalLimitsRecord, self::TraceabilityManage, self::IsoAccess => 'modules',
            self::ProductsManage, self::ProductRequestsCreate => 'products',
            self::BranchesManage, self::UsersManage, self::RolesManage, self::SubscriptionView => 'administration',
        };
    }

    /**
     * The plan module this permission depends on, if any.
     */
    public function module(): ?PlanModule
    {
        return match ($this) {
            self::PrpAccess, self::PrpCustomize => PlanModule::Prp,
            self::HaccpPlansManage => PlanModule::Haccp,
            self::ControlPointsMonitor => PlanModule::Ccp,
            self::CriticalLimitsRecord => PlanModule::Limits,
            self::TraceabilityManage => PlanModule::Trace,
            self::IsoAccess => PlanModule::Iso,
            default => null,
        };
    }

    public function isAvailableOn(?Plan $plan): bool
    {
        $module = $this->module();

        return $module === null || ($plan?->hasModule($module) ?? false);
    }

    public function label(): string
    {
        return __('permissions.client.'.$this->value);
    }

    /**
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

    /**
     * @return list<self>
     */
    public static function availableOn(?Plan $plan): array
    {
        return array_values(array_filter(self::cases(), fn (self $permission): bool => $permission->isAvailableOn($plan)));
    }
}
