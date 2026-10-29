<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ClientUser;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        /** @var ClientUser $user */
        $user = $request->authenticate('client');

        $request->session()->regenerate();
        PortalSession::forget($request->session());
        SetLocale::syncAtLogin($request->session(), $user);
        $user->forceFill(['last_login_at' => now()])->save();

        $user->client->logActivity('portal_login', ['email' => $user->email], $user);

        return redirect()->intended(route('portal.home'));
    }

    /**
     * Signs the client user out. During impersonation the admin session is kept
     * and the admin returns to the client's profile.
     */
    public function destroy(Request $request, PortalSession $portal): RedirectResponse
    {
        $impersonated = $portal->isImpersonated();
        $clientId = $request->user('client')?->client_id;

        Auth::guard('client')->logout();
        PortalSession::forget($request->session());

        if ($impersonated && $clientId !== null) {
            return redirect()->route('admin.clients.show', $clientId);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('status', __('portal.auth.signed_out'));
    }
}
