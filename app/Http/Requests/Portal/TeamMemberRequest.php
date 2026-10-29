<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal;

use App\Http\Requests\ClientUserRequest;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * A company account edited from the portal: on top of the shared rules, only an owner may
 * appoint an owner and nobody may deactivate or demote themselves.
 */
class TeamMemberRequest extends ClientUserRequest
{
    protected function client(): Client
    {
        return $this->user('client')->client;
    }

    protected function member(): ?ClientUser
    {
        return $this->route('teamMember');
    }

    protected function passwordRequiredOnCreate(): bool
    {
        return true;
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $actor = $this->user('client');
            $member = $this->member();
            $wasOwner = (bool) $member?->isOwner();

            if (! $this->isMember() && ! $wasOwner && Gate::forUser($actor)->denies('appointOwner', ClientUser::class)) {
                $validator->errors()->add('role', __('portal.team.errors.owner_only'));
            }

            if ($member?->is($actor) && (! $this->boolean('active') || ($wasOwner && $this->isMember()))) {
                $validator->errors()->add('active', __('portal.team.errors.self_lockout'));
            }
        }];
    }
}
