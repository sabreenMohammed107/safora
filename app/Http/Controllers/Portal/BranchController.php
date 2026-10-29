<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Switches the working branch. Users may only pick branches they can access;
 * only the company manager may pick "all branches".
 */
class BranchController extends Controller
{
    public function update(Request $request, PortalSession $portal): RedirectResponse
    {
        $request->validate(['branch' => ['required', 'string', 'max:20']]);

        abort_unless($portal->select($request->string('branch')->value()), 403);

        return back();
    }
}
