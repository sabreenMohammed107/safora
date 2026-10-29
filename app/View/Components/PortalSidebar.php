<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\ClientPermission;
use App\Enums\PlanModule;
use App\Models\Plan;
use App\Services\PortalSession;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Component;

/**
 * Client portal navigation (docs/sprints/02_CLIENT_PORTAL.md). Items outside the client's plan
 * show a lock; items whose screens arrive in later sprints are listed but disabled.
 */
class PortalSidebar extends Component
{
    /**
     * @var list<array{key: string, icon: string, label: string, sub?: string, items: list<array{key: string, icon: string, label: string, route: ?string, locked: bool}>}>
     */
    public array $groups;

    /**
     * @var list<array{key: string, icon: string, label: string, route: ?string, locked: bool}>
     */
    public array $top;

    /**
     * @var list<array{key: string, icon: string, label: string, route: ?string, locked: bool}>
     */
    public array $bottom;

    public function __construct(public PortalSession $portal)
    {
        $plan = $portal->user()->client->subscription?->plan;

        $item = fn (string $key, string $icon, ?PlanModule $module = null, ?string $route = null): array => [
            'key' => $key,
            'icon' => $icon,
            'label' => __('portal.nav.'.$key),
            'route' => $route,
            'locked' => $module !== null && ! $this->planHas($plan, $module),
        ];

        $this->top = [
            $item('home', 'fa-house', route: 'portal.home'),
            $item('products', 'fa-boxes-stacked'),
        ];

        $this->groups = [
            [
                'key' => 'haccp', 'icon' => 'fa-shield-heart', 'label' => __('portal.nav.haccp_pack'), 'sub' => 'HACCP',
                'items' => [
                    $item('prp', 'fa-layer-group', PlanModule::Prp),
                    $item('haccp_plans', 'fa-diagram-project', PlanModule::Haccp),
                    $item('control_points', 'fa-crosshairs', PlanModule::Ccp),
                    $item('critical_limits', 'fa-temperature-half', PlanModule::Limits),
                    $item('traceability', 'fa-route', PlanModule::Trace),
                ],
            ],
            [
                'key' => 'iso', 'icon' => 'fa-award', 'label' => __('portal.nav.iso_pack'), 'sub' => 'ISO 22000',
                'items' => [
                    $item('iso_procedures', 'fa-file-signature', PlanModule::Iso),
                    $item('iso_items', 'fa-list-check', PlanModule::Iso),
                ],
            ],
        ];

        $gate = Gate::forUser($portal->user());

        $this->bottom = [
            $item('product_requests', 'fa-inbox'),
            ...($gate->allows(ClientPermission::BranchesManage->value)
                ? [$item('branches', 'fa-store', route: 'portal.branches.index')]
                : []),
            ...($gate->any([ClientPermission::UsersManage->value, ClientPermission::RolesManage->value])
                ? [$item('team', 'fa-users-gear', route: 'portal.team.index')]
                : []),
            $item('account', 'fa-user-gear', route: 'portal.account.edit'),
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.portal-sidebar');
    }

    private function planHas(?Plan $plan, PlanModule $module): bool
    {
        return $plan?->loadMissing('modules', 'includedPlan.modules')->hasModule($module) ?? false;
    }
}
