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

    Route::middleware(['auth:admin', 'admin.active'])->group(function () {
        Route::post('logout', [Admin\Auth\SessionController::class, 'destroy'])->name('logout');

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

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

        Route::resource('staff', Admin\StaffController::class)->except(['show', 'destroy'])->parameters(['staff' => 'staff']);
        Route::resource('roles', Admin\RoleController::class)->except(['index', 'show']);
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

    Route::middleware('auth:client')->group(function () {
        Route::post('logout', [Portal\Auth\SessionController::class, 'destroy'])->name('logout');
        Route::get('blocked', Portal\BlockedController::class)->name('blocked');

        Route::middleware('portal.access')->group(function () {
            Route::get('/', Portal\HomeController::class)->name('home');
            Route::put('branch', [Portal\BranchController::class, 'update'])->name('branch.update');

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
