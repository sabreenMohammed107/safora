<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PortalSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends accounts holding a temporary password (set by an admin or company manager) to their
 * profile page until they choose their own. Usage: `password.change:admin` / `password.change:client`.
 * An admin impersonating a client is never forced to change the client's password.
 */
class RequirePasswordChange
{
    /**
     * Routes that stay reachable while a password change is pending.
     *
     * @var array<string, list<string>>
     */
    private const array ALLOWED = [
        'admin' => ['admin.profile.edit', 'admin.profile.password', 'admin.logout'],
        'client' => ['portal.account.edit', 'portal.account.password', 'portal.logout', 'portal.blocked'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $user = $request->user($guard);

        if (! $user?->must_change_password
            || $request->routeIs(...self::ALLOWED[$guard])
            || ($guard === 'client' && $request->session()->has(PortalSession::IMPERSONATOR_KEY))) {
            return $next($request);
        }

        return redirect()->route($guard === 'admin' ? 'admin.profile.edit' : 'portal.account.edit')
            ->with('error', __('accounts.must_change_password'));
    }
}
