<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out staff whose account was deactivated, including sessions opened before the change.
 */
class EnsureAdminIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('admin')?->active === false) {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')->withErrors(['email' => __('admin.auth.inactive')]);
        }

        return $next($request);
    }
}
