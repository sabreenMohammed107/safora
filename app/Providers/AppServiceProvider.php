<?php

namespace App\Providers;

use App\Enums\AdminPermission;
use App\Enums\ClientPermission;
use App\Models\Admin;
use App\Models\ClientBranch;
use App\Models\ClientRole;
use App\Models\ClientUser;
use App\Services\PortalSession;
use App\Services\PricingService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PricingService::class, fn (): PricingService => new PricingService(
            vatRate: (float) config('safora.vat_rate'),
            currency: (string) config('safora.currency'),
        ));

        $this->app->scoped(PortalSession::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn (): Password => Password::min(8)->letters()->numbers());

        $this->registerPermissionGates();
        $this->registerTenantBindings();
    }

    /**
     * Every AdminPermission / ClientPermission value is a Gate ability for its user type, so
     * `can:clients.approve` middleware and `@can('users.manage')` work with the dynamic roles.
     * This only grants; anything else falls through to model policies and is denied by default.
     */
    private function registerPermissionGates(): void
    {
        Gate::before(function (Authenticatable $user, string $ability): ?bool {
            $granted = match (true) {
                $user instanceof Admin => ($permission = AdminPermission::tryFrom($ability)) && $user->hasPermission($permission),
                $user instanceof ClientUser => ($permission = ClientPermission::tryFrom($ability)) && $user->hasPermission($permission),
                default => false,
            };

            return $granted ?: null;
        });
    }

    /**
     * Portal route parameters resolve only within the signed-in user's company, so another
     * tenant's ids return 404 instead of revealing that the record exists.
     */
    private function registerTenantBindings(): void
    {
        $tenantClientId = fn (): ?int => Auth::guard('client')->user()?->client_id;

        Route::bind('teamRole', fn (string $value): ClientRole => ClientRole::query()
            ->where('client_id', $tenantClientId())
            ->findOrFail($value));

        Route::bind('teamMember', fn (string $value): ClientUser => ClientUser::query()
            ->where('client_id', $tenantClientId())
            ->findOrFail($value));

        Route::bind('companyBranch', fn (string $value): ClientBranch => ClientBranch::query()
            ->where('client_id', $tenantClientId())
            ->findOrFail($value));
    }
}
