<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $password = $request->newPassword() ?? Admin::generatePassword();

        $staff = new Admin($request->staffData());
        $staff->is_super_admin = $request->wantsSuperAdmin();
        $staff->assignTemporaryPassword($password)->save();

        return redirect()->route('admin.staff.index')
            ->with('status', __('admin.staff.flash.created', ['name' => $staff->name]))
            ->with('credentials', $this->credentials($staff, $password));
    }

    /**
     * Issues a new temporary password for another staff member (shown once).
     * Staff change their own password from their profile instead.
     */
    public function resetPassword(Request $request, Admin $staff): RedirectResponse
    {
        Gate::authorize('update', $staff);
        abort_if($staff->is($request->user('admin')), 403);

        $password = Admin::generatePassword();
        $staff->assignTemporaryPassword($password)->save();

        return back()
            ->with('status', __('accounts.flash.password_reset', ['name' => $staff->name]))
            ->with('credentials', $this->credentials($staff, $password));
    }

    public function edit(Admin $staff): View
    {
        Gate::authorize('update', $staff);

        return $this->form($staff);
    }

    public function update(StaffRequest $request, Admin $staff): RedirectResponse
    {
        Gate::authorize('update', $staff);

        $isSelf = $staff->is($request->user('admin'));
        $password = $request->newPassword();

        $staff->fill($request->staffData());
        $staff->is_super_admin = $request->wantsSuperAdmin();

        if ($password !== null) {
            $isSelf ? $staff->assignOwnPassword($password) : $staff->assignTemporaryPassword($password);
        }

        $staff->save();

        if ($isSelf) {
            // Keeps this session valid after changing one's own password (auth.session re-reads this instance).
            $request->user('admin')->refresh();
        }

        return redirect()->route('admin.staff.index')
            ->with('status', __('admin.staff.flash.updated', ['name' => $staff->name]))
            ->with('credentials', $password !== null && ! $isSelf ? $this->credentials($staff, $password) : null);
    }

    /**
     * @return array{name: string, email: string, password: string, login_url: string}
     */
    private function credentials(Admin $staff, string $password): array
    {
        return ['name' => $staff->name, 'email' => $staff->email, 'password' => $password, 'login_url' => route('admin.login')];
    }

    private function form(Admin $staff): View
    {
        return view('admin.staff.form', [
            'staff' => $staff,
            'roles' => AdminRole::query()->orderBy('name')->get(),
        ]);
    }
}
