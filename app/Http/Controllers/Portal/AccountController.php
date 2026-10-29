<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * "My account": the signed-in portal user's name, language and password (02_CLIENT_PORTAL.md).
 * An impersonating admin may view it but never change the client's details or password.
 */
class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('portal.account.edit', ['user' => $request->user('client')]);
    }

    public function update(Request $request, PortalSession $portal): RedirectResponse
    {
        abort_if($portal->isImpersonated(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', Rule::in(array_keys(config('safora.locales')))],
        ]);

        $request->user('client')->update($data);
        $request->session()->put(SetLocale::SESSION_KEY, $data['locale']);

        return back()->with('status', __('accounts.flash.profile_saved'));
    }

    public function updatePassword(UpdateOwnPasswordRequest $request, PortalSession $portal): RedirectResponse
    {
        abort_if($portal->isImpersonated(), 403);

        $request->user('client')->assignOwnPassword($request->newPassword())->save();

        return redirect()->route('portal.home')->with('status', __('accounts.flash.password_changed'));
    }
}
