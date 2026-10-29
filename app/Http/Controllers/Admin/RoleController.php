<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRoleRequest;
use App\Models\AdminRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Staff roles and their permissions.
 */
class RoleController extends Controller
{
    public function create(Request $request): View
    {
        Gate::authorize('create', AdminRole::class);

        return $this->form($request, new AdminRole(['permissions' => []]));
    }

    public function store(AdminRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', AdminRole::class);

        $role = AdminRole::create($request->roleData());

        return redirect()->route('admin.staff.index')->with('status', __('admin.roles.flash.created', ['name' => $role->name]));
    }

    public function edit(Request $request, AdminRole $role): View
    {
        Gate::authorize('update', $role);

        return $this->form($request, $role);
    }

    public function update(AdminRoleRequest $request, AdminRole $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $role->update($request->roleData());

        return redirect()->route('admin.staff.index')->with('status', __('admin.roles.flash.updated', ['name' => $role->name]));
    }

    public function destroy(AdminRole $role): RedirectResponse
    {
        if (Gate::denies('delete', $role)) {
            return back()->with('error', __('admin.roles.flash.in_use'));
        }

        $role->delete();

        return redirect()->route('admin.staff.index')->with('status', __('admin.roles.flash.deleted', ['name' => $role->name]));
    }

    private function form(Request $request, AdminRole $role): View
    {
        $grantable = AdminRoleRequest::grantableBy($request->user('admin'));

        return view('admin.roles.form', [
            'role' => $role,
            'groups' => collect(AdminPermission::grouped())->map(fn (array $permissions): array => array_map(fn (AdminPermission $permission): array => [
                'value' => $permission->value,
                'label' => $permission->label(),
                'disabled' => ! in_array($permission, $grantable, true),
                'hint' => null,
            ], $permissions))->all(),
        ]);
    }
}
