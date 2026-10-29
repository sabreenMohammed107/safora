<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Enums\ClientUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\TeamMemberRequest;
use App\Models\ClientRole;
use App\Models\ClientUser;
use App\Services\PortalSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Company accounts managed from the portal. Accounts are deactivated, never deleted,
 * so records stay attributable.
 */
class TeamMemberController extends Controller
{
    public function __construct(private readonly PortalSession $portal) {}

    public function create(): View
    {
        Gate::authorize('create', ClientUser::class);

        return $this->form(new ClientUser(['role' => ClientUserRole::Member, 'active' => true]));
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        Gate::authorize('create', ClientUser::class);

        $client = $this->portal->user()->client;
        $member = $client->users()->create([...$request->memberData(), 'locale' => app()->getLocale()]);

        $client->logActivity('team_member_created', ['name' => $member->name], $this->portal->user());

        return redirect()->route('portal.team.index')->with('status', __('portal.team.flash.member_created', ['name' => $member->name]));
    }

    public function edit(ClientUser $teamMember): View
    {
        Gate::authorize('update', $teamMember);

        return $this->form($teamMember);
    }

    public function update(TeamMemberRequest $request, ClientUser $teamMember): RedirectResponse
    {
        Gate::authorize('update', $teamMember);

        $teamMember->update($request->memberData());

        $teamMember->client->logActivity('team_member_updated', ['name' => $teamMember->name], $this->portal->user());

        return redirect()->route('portal.team.index')->with('status', __('portal.team.flash.member_updated', ['name' => $teamMember->name]));
    }

    private function form(ClientUser $member): View
    {
        $client = $this->portal->user()->client;

        return view('portal.team.member-form', [
            'member' => $member,
            'branches' => $client->branches()->orderBy('code')->get(),
            'roles' => ClientRole::sortForDisplay($client->roles()->get()),
            'canAppointOwner' => Gate::allows('appointOwner', ClientUser::class),
        ]);
    }
}
