<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\PortalSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends signed-in client users to the "blocked" screen while their company or account
 * cannot use the portal, otherwise resolves their branch selection for the request.
 */
class EnsurePortalAccess
{
    public function __construct(private readonly PortalSession $portal) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('client');

        if ($user->portalBlock($this->portal->isImpersonated()) !== null) {
            return redirect()->route('portal.blocked');
        }

        $this->portal->start($user);

        View::share('portal', $this->portal);

        return $next($request);
    }
}
