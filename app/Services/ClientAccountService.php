<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClientUserRole;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Branches and portal accounts of a client, shared by the admin client profile and the portal
 * (docs/sprints/03_BUSINESS_RULES.md §2–3). Each change locks the client row so concurrent
 * edits cannot both remove "the second-to-last" branch or owner.
 *
 * Rules: the last active branch cannot be deactivated (deactivating never deletes records);
 * at least one active owner must remain; the plan's max_sites / max_users (null = unlimited)
 * cap active branches / users.
 */
class ClientAccountService
{
    /**
     * @param  array{name: string, city?: ?string, address?: ?string, manager?: ?string, phone?: ?string, active: bool}  $data
     */
    public function createBranch(Client $client, array $data, ?Model $actor = null): ClientBranch
    {
        return DB::transaction(function () use ($client, $data, $actor): ClientBranch {
            $this->lock($client);

            if ($data['active']) {
                $this->assertBranchCapacity($client);
            }

            $branch = $client->branches()->create([...$data, 'code' => $client->nextBranchCode()]);
            $client->logActivity('branch_created', ['name' => $branch->name], $actor);

            return $branch;
        });
    }

    /**
     * @param  array{name: string, city?: ?string, address?: ?string, manager?: ?string, phone?: ?string, active: bool}  $data
     */
    public function updateBranch(ClientBranch $branch, array $data, ?Model $actor = null): ClientBranch
    {
        return DB::transaction(function () use ($branch, $data, $actor): ClientBranch {
            $client = $branch->client;
            $this->lock($client);
            $branch->refresh();

            if ($branch->active && ! $data['active'] && $client->branches()->active()->whereKeyNot($branch->getKey())->doesntExist()) {
                throw ValidationException::withMessages(['active' => __('accounts.errors.last_branch')]);
            }

            if (! $branch->active && $data['active']) {
                $this->assertBranchCapacity($client);
            }

            $wasActive = $branch->active;
            $branch->update($data);

            $event = match (true) {
                $wasActive && ! $branch->active => 'branch_deactivated',
                ! $wasActive && $branch->active => 'branch_activated',
                default => 'branch_updated',
            };
            $client->logActivity($event, ['name' => $branch->name], $actor);

            return $branch;
        });
    }

    /**
     * @param  array{name: string, email: string, role: ClientUserRole, branch_id: ?int, client_role_id: ?int, active: bool, password?: string}  $data
     */
    public function createUser(Client $client, array $data, ?Model $actor = null): ClientUser
    {
        return DB::transaction(function () use ($client, $data, $actor): ClientUser {
            $this->lock($client);

            if ($data['active']) {
                $this->assertUserCapacity($client);
            }

            $password = $data['password'] ?? null;
            unset($data['password']);

            $user = new ClientUser([...$data, 'client_id' => $client->getKey(), 'locale' => app()->getLocale()]);
            $user->assignTemporaryPassword($password ?? ClientUser::generatePassword())->save();

            $client->logActivity('team_member_created', ['name' => $user->name], $actor);

            return $user;
        });
    }

    /**
     * Updates an account. A password given here was chosen by someone else (admin or manager)
     * unless the actor is the account holder.
     *
     * @param  array{name: string, email: string, role: ClientUserRole, branch_id: ?int, client_role_id: ?int, active: bool, password?: string}  $data
     */
    public function updateUser(ClientUser $user, array $data, ?Model $actor = null): ClientUser
    {
        return DB::transaction(function () use ($user, $data, $actor): ClientUser {
            $client = $user->client;
            $this->lock($client);
            $user->refresh();

            $losesOwner = $user->isOwner() && $user->active && ($data['role'] !== ClientUserRole::Owner || ! $data['active']);

            if ($losesOwner && $this->otherActiveOwners($user)->doesntExist()) {
                throw ValidationException::withMessages(['role' => __('accounts.errors.last_owner')]);
            }

            if (! $user->active && $data['active']) {
                $this->assertUserCapacity($client);
            }

            $password = $data['password'] ?? null;
            unset($data['password']);

            $user->fill($data);

            if ($password !== null) {
                $user->is($actor) ? $user->assignOwnPassword($password) : $user->assignTemporaryPassword($password);
            }

            $user->save();
            $client->logActivity('team_member_updated', ['name' => $user->name], $actor);

            return $user;
        });
    }

    /**
     * Issues a new temporary password for a portal account and returns it (shown once to the admin).
     */
    public function resetPassword(ClientUser $user, ?Model $actor = null): string
    {
        $password = ClientUser::generatePassword();

        $user->assignTemporaryPassword($password)->save();
        $user->client->logActivity('password_reset', ['name' => $user->name], $actor);

        return $password;
    }

    private function lock(Client $client): void
    {
        Client::query()->whereKey($client->getKey())->lockForUpdate()->first();
    }

    private function assertBranchCapacity(Client $client): void
    {
        $limit = $client->subscription?->plan?->max_sites;

        if ($limit !== null && $client->branches()->active()->count() >= $limit) {
            throw ValidationException::withMessages(['active' => __('accounts.errors.branch_limit', ['max' => $limit])]);
        }
    }

    private function assertUserCapacity(Client $client): void
    {
        $limit = $client->subscription?->plan?->max_users;

        if ($limit !== null && $client->users()->where('active', true)->count() >= $limit) {
            throw ValidationException::withMessages(['active' => __('accounts.errors.user_limit', ['max' => $limit])]);
        }
    }

    /**
     * @return Builder<ClientUser>
     */
    private function otherActiveOwners(ClientUser $user): Builder
    {
        return ClientUser::query()
            ->where('client_id', $user->client_id)
            ->where('role', ClientUserRole::Owner)
            ->where('active', true)
            ->whereKeyNot($user->getKey());
    }
}
