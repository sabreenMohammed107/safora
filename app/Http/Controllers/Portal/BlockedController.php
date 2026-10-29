<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Explains why the portal is closed (pending review, suspended, expired, no branch…).
 */
class BlockedController extends Controller
{
    public function __invoke(Request $request, PortalSession $portal): View|RedirectResponse
    {
        $user = $request->user('client');
        $block = $user->portalBlock($portal->isImpersonated());

        if ($block === null) {
            return redirect()->route('portal.home');
        }

        return view('portal.blocked', [
            'user' => $user,
            'client' => $user->client,
            'block' => $block,
            'impersonated' => $portal->isImpersonated(),
        ]);
    }
}
