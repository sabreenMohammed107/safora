<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * AR/EN switcher. Saves the choice in the session and on the signed-in account(s) so it is
 * restored at their next login. An impersonating admin never changes the client user's setting.
 */
class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(SetLocale::isSupported($locale), 404);

        $request->session()->put(SetLocale::SESSION_KEY, $locale);

        $request->user('admin')?->forceFill(['locale' => $locale])->save();

        if (! $request->session()->has(PortalSession::IMPERSONATOR_KEY)) {
            $request->user('client')?->forceFill(['locale' => $locale])->save();
        }

        return back();
    }
}
