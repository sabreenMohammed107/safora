<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ClientUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientUserRequest;
use App\Models\Client;
use App\Models\ClientRole;
use App\Models\ClientUser;
use App\Services\ClientAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Portal accounts managed from the admin client profile, including temporary passwords
 * shown once with a ready-to-copy message (01_ADMIN.md §E).
 */
class ClientUserController extends Controller
{
    public function __construct(private readonly ClientAccountService $accounts) {}

    public function create(Client $client): View
    {
        return $this->form($client, new ClientUser(['role' => ClientUserRole::Member, 'active' => true]));
    }

    public function store(ClientUserRequest $request, Client $client): RedirectResponse
    {
        $data = $request->memberData();
        $password = $data['password'] ?? ClientUser::generatePassword();

        $user = $this->accounts->createUser($client, [...$data, 'password' => $password], $request->user('admin'));

        return redirect()->route('admin.clients.show', $client)
            ->with('status', __('portal.team.flash.member_created', ['name' => $user->name]))
            ->with('credentials', $this->credentials($client, $user, $password));
    }

    public function edit(Client $client, ClientUser $user): View
    {
        return $this->form($client, $user);
    }

    public function update(ClientUserRequest $request, Client $client, ClientUser $user): RedirectResponse
    {
        $data = $request->memberData();

        $this->accounts->updateUser($user, $data, $request->user('admin'));

        return redirect()->route('admin.clients.show', $client)
            ->with('status', __('portal.team.flash.member_updated', ['name' => $user->name]))
            ->with('credentials', isset($data['password']) ? $this->credentials($client, $user, $data['password']) : null);
    }

    public function resetPassword(Request $request, Client $client, ClientUser $user): RedirectResponse
    {
        $password = $this->accounts->resetPassword($user, $request->user('admin'));

        return redirect()->route('admin.clients.show', $client)
            ->with('status', __('accounts.flash.password_reset', ['name' => $user->name]))
            ->with('credentials', $this->credentials($client, $user, $password));
    }

    /**
     * @return array{name: string, email: string, password: string, login_url: string, company: string}
     */
    private function credentials(Client $client, ClientUser $user, string $password): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'login_url' => route('portal.login'),
            'company' => $client->company,
        ];
    }

    private function form(Client $client, ClientUser $user): View
    {
        return view('admin.clients.user-form', [
            'client' => $client,
            'member' => $user,
            'branches' => $client->branches()->orderBy('code')->get(),
            'roles' => ClientRole::sortForDisplay($client->roles()->get()),
        ]);
    }
}
