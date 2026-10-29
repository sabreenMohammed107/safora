<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/portal');

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

/*
|--------------------------------------------------------------------------
| Admin (guard: admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [Admin\Auth\SessionController::class, 'create'])->name('login');
        Route::post('login', [Admin\Auth\SessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', 'admin.active', 'auth.session', 'password.change:admin'])->group(function () {
        Route::post('logout', [Admin\Auth\SessionController::class, 'destroy'])->name('logout');

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::middleware('can:clients.view')->group(function () {
            Route::get('clients', [Admin\ClientController::class, 'index'])->name('clients.index');
            Route::get('clients/{client}', [Admin\ClientController::class, 'show'])->name('clients.show');
        });

        Route::middleware('can:clients.approve')->group(function () {
            Route::post('clients/{client}/approve', [Admin\ClientStatusController::class, 'approve'])->name('clients.approve');
            Route::post('clients/{client}/reject', [Admin\ClientStatusController::class, 'reject'])->name('clients.reject');
        });

        Route::middleware('can:clients.suspend')->group(function () {
            Route::post('clients/{client}/suspend', [Admin\ClientStatusController::class, 'suspend'])->name('clients.suspend');
            Route::post('clients/{client}/reactivate', [Admin\ClientStatusController::class, 'reactivate'])->name('clients.reactivate');
        });

        Route::get('clients/{client}/documents/{document}', [Admin\ClientDocumentController::class, 'show'])
            ->scopeBindings()
            ->middleware('can:clients.documents')
            ->name('clients.documents.show');

        Route::post('clients/{client}/impersonate', [Admin\ImpersonationController::class, 'store'])
            ->middleware('can:clients.impersonate')
            ->name('clients.impersonate');

        Route::middleware('can:clients.accounts')->scopeBindings()->group(function () {
            Route::get('clients/{client}/branches/create', [Admin\ClientBranchController::class, 'create'])->name('clients.branches.create');
            Route::post('clients/{client}/branches', [Admin\ClientBranchController::class, 'store'])->name('clients.branches.store');
            Route::get('clients/{client}/branches/{branch}/edit', [Admin\ClientBranchController::class, 'edit'])->name('clients.branches.edit');
            Route::put('clients/{client}/branches/{branch}', [Admin\ClientBranchController::class, 'update'])->name('clients.branches.update');

            Route::get('clients/{client}/users/create', [Admin\ClientUserController::class, 'create'])->name('clients.users.create');
            Route::post('clients/{client}/users', [Admin\ClientUserController::class, 'store'])->name('clients.users.store');
            Route::get('clients/{client}/users/{user}/edit', [Admin\ClientUserController::class, 'edit'])->name('clients.users.edit');
            Route::put('clients/{client}/users/{user}', [Admin\ClientUserController::class, 'update'])->name('clients.users.update');
            Route::post('clients/{client}/users/{user}/password', [Admin\ClientUserController::class, 'resetPassword'])->name('clients.users.password');
        });

        Route::middleware('can:clients.sectors')->group(function () {
            Route::post('clients/{client}/sectors', [Admin\ClientSectorController::class, 'store'])->name('clients.sectors.store');
            Route::delete('clients/{client}/sectors/{sector}', [Admin\ClientSectorController::class, 'destroy'])->name('clients.sectors.destroy');
        });

        Route::resource('staff', Admin\StaffController::class)->except(['show', 'destroy'])->parameters(['staff' => 'staff']);
        Route::post('staff/{staff}/password', [Admin\StaffController::class, 'resetPassword'])->name('staff.password');
        Route::resource('roles', Admin\RoleController::class)->except(['index', 'show']);

        Route::middleware('can:haccp.manage')->prefix('haccp')->name('haccp.')->group(function () {
            Route::get('programs', [Admin\HaccpProgramController::class, 'index'])->name('programs.index');
            Route::post('programs', [Admin\HaccpProgramController::class, 'store'])->name('programs.store');
            Route::put('programs/{program:code}', [Admin\HaccpProgramController::class, 'update'])->name('programs.update');
            Route::delete('programs/{program:code}', [Admin\HaccpProgramController::class, 'destroy'])->name('programs.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Client portal (guard: client)
|--------------------------------------------------------------------------
*/

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('login', [Portal\Auth\SessionController::class, 'create'])->name('login');
        Route::post('login', [Portal\Auth\SessionController::class, 'store'])->name('login.store');

        Route::get('register', [Portal\RegistrationController::class, 'create'])->name('register');
        Route::post('register', [Portal\RegistrationController::class, 'store'])->name('register.store');
    });

    Route::middleware(['auth:client', 'auth.session', 'password.change:client'])->group(function () {
        Route::post('logout', [Portal\Auth\SessionController::class, 'destroy'])->name('logout');
        Route::get('blocked', Portal\BlockedController::class)->name('blocked');

        Route::middleware('portal.access')->group(function () {
            Route::get('/', Portal\HomeController::class)->name('home');
            Route::put('branch', [Portal\BranchController::class, 'update'])->name('branch.update');

            Route::get('account', [Portal\AccountController::class, 'edit'])->name('account.edit');
            Route::put('account', [Portal\AccountController::class, 'update'])->name('account.update');
            Route::put('account/password', [Portal\AccountController::class, 'updatePassword'])->name('account.password');

            Route::middleware('can:branches.manage')->group(function () {
                Route::get('branches', [Portal\CompanyBranchController::class, 'index'])->name('branches.index');
                Route::get('branches/create', [Portal\CompanyBranchController::class, 'create'])->name('branches.create');
                Route::post('branches', [Portal\CompanyBranchController::class, 'store'])->name('branches.store');
                Route::get('branches/{companyBranch}/edit', [Portal\CompanyBranchController::class, 'edit'])->name('branches.edit');
                Route::put('branches/{companyBranch}', [Portal\CompanyBranchController::class, 'update'])->name('branches.update');
            });

            Route::get('team', Portal\TeamController::class)->name('team.index');

            Route::middleware('can:users.manage')->group(function () {
                Route::get('team/members/create', [Portal\TeamMemberController::class, 'create'])->name('team.members.create');
                Route::post('team/members', [Portal\TeamMemberController::class, 'store'])->name('team.members.store');
                Route::get('team/members/{teamMember}/edit', [Portal\TeamMemberController::class, 'edit'])->name('team.members.edit');
                Route::put('team/members/{teamMember}', [Portal\TeamMemberController::class, 'update'])->name('team.members.update');
            });

            Route::middleware('can:roles.manage')->group(function () {
                Route::get('team/roles/create', [Portal\TeamRoleController::class, 'create'])->name('team.roles.create');
                Route::post('team/roles', [Portal\TeamRoleController::class, 'store'])->name('team.roles.store');
                Route::get('team/roles/{teamRole}/edit', [Portal\TeamRoleController::class, 'edit'])->name('team.roles.edit');
                Route::put('team/roles/{teamRole}', [Portal\TeamRoleController::class, 'update'])->name('team.roles.update');
                Route::delete('team/roles/{teamRole}', [Portal\TeamRoleController::class, 'destroy'])->name('team.roles.destroy');
            });
        });
    });
});
