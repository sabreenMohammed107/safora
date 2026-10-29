<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SAFORA staff accounts. Accounts are deactivated, never deleted, so their history stays attributable.
 */
class StaffController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Admin::class);

        return view('admin.staff.index', [
            'staff' => Admin::query()->with('role')->orderByDesc('is_super_admin')->orderBy('name')->get(),
            'roles' => AdminRole::query()->withCount('admins')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Admin::class);

        return $this->form(new Admin(['active' => true, 'locale' => 'ar']));
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        Gate::authorize('create', Admin::class);

        $staff = new Admin($request->staffData());
        $staff->is_super_admin = $request->wantsSuperAdmin();
        $staff->save();

        return redirect()->route('admin.staff.index')->with('status', __('admin.staff.flash.created', ['name' => $staff->name]));
    }

    public function edit(Admin $staff): View
    {
        Gate::authorize('update', $staff);

        return $this->form($staff);
    }

    public function update(StaffRequest $request, Admin $staff): RedirectResponse
    {
        Gate::authorize('update', $staff);

        $staff->fill($request->staffData());
        $staff->is_super_admin = $request->wantsSuperAdmin();
        $staff->save();

        return redirect()->route('admin.staff.index')->with('status', __('admin.staff.flash.updated', ['name' => $staff->name]));
    }

    private function form(Admin $staff): View
    {
        return view('admin.staff.form', [
            'staff' => $staff,
            'roles' => AdminRole::query()->orderBy('name')->get(),
        ]);
    }
}
