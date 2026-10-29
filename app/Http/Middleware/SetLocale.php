<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's language (Arabic/English) from the session. The session value is set
 * by the AR/EN switcher and at sign-in (see syncAtLogin()).
 */
class SetLocale
{
    public const string SESSION_KEY = 'locale';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get(self::SESSION_KEY);

        if (is_string($locale) && self::isSupported($locale)) {
            App::setLocale($locale);
        }

        Carbon::setLocale(App::getLocale());

        return $next($request);
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, config('safora.locales'));
    }

    /**
     * At sign-in: a language picked on the sign-in screen is kept and saved to the account;
     * otherwise the account's saved language is restored. The caller saves the account.
     */
    public static function syncAtLogin(Session $session, Model $account): void
    {
        $chosen = $session->get(self::SESSION_KEY);

        if (is_string($chosen) && self::isSupported($chosen)) {
            $account->forceFill(['locale' => $chosen]);

            return;
        }

        $session->put(self::SESSION_KEY, $account->getAttribute('locale'));
    }
}
