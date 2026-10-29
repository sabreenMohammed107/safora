<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Log in as client": opens the portal as the company manager (owner) while the admin stays signed in.
 */
class ImpersonationController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $owner = $client->owner;

        if ($owner === null) {
            return back()->with('error', __('admin.clients.flash.no_owner'));
        }

        Auth::guard('client')->login($owner);

        PortalSession::forget($request->session());
        $request->session()->put(PortalSession::IMPERSONATOR_KEY, $request->user('admin')->getKey());

        $client->logActivity('impersonated', actor: $request->user('admin'));

        return redirect()->route('portal.home');
    }
}
