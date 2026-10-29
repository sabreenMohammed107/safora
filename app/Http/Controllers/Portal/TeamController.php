<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Enums\ClientPermission;
use App\Http\Controllers\Controller;
use App\Models\ClientRole;
use App\Models\ClientUser;
use App\Services\PortalSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * "Team & roles": the company's portal accounts and the roles that define what they can do.
 */
class TeamController extends Controller
{
    public function __invoke(PortalSession $portal): View
    {
        $actor = $portal->user();
        $client = $actor->client;

        $canManageMembers = Gate::allows('viewAny', ClientUser::class);
        $canManageRoles = Gate::allows('viewAny', ClientRole::class);

        abort_unless($canManageMembers || $canManageRoles, 403);

        return view('portal.team.index', [
            'members' => $canManageMembers
                ? $client->users()->with(['branch', 'clientRole'])->orderBy('role')->orderBy('name')->get()
                : collect(),
            'roles' => $canManageRoles
                ? ClientRole::sortForDisplay($client->roles()->withCount('users')->get())
                : collect(),
            'plan' => $client->subscription?->plan,
            'permissionCount' => count(ClientPermission::availableOn($client->subscription?->plan)),
        ]);
    }
}
