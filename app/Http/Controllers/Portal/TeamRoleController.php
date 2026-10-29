<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Enums\ClientPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\ClientRoleRequest;
use App\Models\ClientRole;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Company roles managed by the company manager (or employees with roles.manage).
 */
class TeamRoleController extends Controller
{
    public function __construct(private readonly PortalSession $portal) {}

    public function create(): View
    {
        Gate::authorize('create', ClientRole::class);

        return $this->form(new ClientRole(['permissions' => []]));
    }

    public function store(ClientRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', ClientRole::class);

        $client = $this->portal->user()->client;
        $role = $client->roles()->create($request->roleData());

        $client->logActivity('role_created', ['name' => $role->name], $this->portal->user());

        return redirect()->route('portal.team.index')->with('status', __('portal.team.flash.role_created', ['name' => $role->name]));
    }

    public function edit(ClientRole $teamRole): View
    {
        Gate::authorize('update', $teamRole);

        return $this->form($teamRole);
    }

    /**
     * Permissions outside the current plan are not editable but are kept, so they come back
     * if the client upgrades. Renaming a default role turns it into a custom one.
     */
    public function update(ClientRoleRequest $request, ClientRole $teamRole): RedirectResponse
    {
        Gate::authorize('update', $teamRole);

        $data = $request->roleData();
        $plan = $this->portal->user()->client->subscription?->plan;
        $keptOutsidePlan = $teamRole->permissions->reject(fn (ClientPermission $permission): bool => $permission->isAvailableOn($plan));

        $teamRole->fill([...$data, 'permissions' => [...$data['permissions'], ...$keptOutsidePlan]]);

        if ($teamRole->key !== null && $data['name'] !== $teamRole->displayName()) {
            $teamRole->key = null;
        }

        $teamRole->save();
        $teamRole->client->logActivity('role_updated', ['name' => $teamRole->displayName()], $this->portal->user());

        return redirect()->route('portal.team.index')->with('status', __('portal.team.flash.role_updated', ['name' => $teamRole->displayName()]));
    }

    public function destroy(ClientRole $teamRole): RedirectResponse
    {
        if (Gate::denies('delete', $teamRole)) {
            return back()->with('error', __('portal.team.flash.role_in_use'));
        }

        $name = $teamRole->displayName();
        $teamRole->delete();
        $this->portal->user()->client->logActivity('role_deleted', ['name' => $name], $this->portal->user());

        return redirect()->route('portal.team.index')->with('status', __('portal.team.flash.role_deleted', ['name' => $name]));
    }

    private function form(ClientRole $role): View
    {
        $actor = $this->portal->user();
        $plan = $actor->client->subscription?->plan;
        $grantable = ClientRoleRequest::grantableBy($actor);

        return view('portal.team.role-form', [
            'role' => $role,
            'groups' => collect(ClientPermission::grouped())->map(fn (array $permissions): array => array_map(fn (ClientPermission $permission): array => [
                'value' => $permission->value,
                'label' => $permission->label(),
                'disabled' => ! in_array($permission, $grantable, true),
                'hint' => $permission->isAvailableOn($plan) ? null : __('portal.team.outside_plan'),
            ], $permissions))->all(),
        ]);
    }
}
