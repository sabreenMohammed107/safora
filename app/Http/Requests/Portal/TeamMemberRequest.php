<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal;

use App\Enums\ClientUserRole;
use App\Models\ClientUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Create or update a company portal account (docs/sprints/03_BUSINESS_RULES.md §3):
 * members need a branch and a role of this company; only an owner may appoint an owner;
 * nobody may lock themselves out; at least one active owner must remain; emails are unique
 * across every client.
 */
class TeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request (checked by the controller's policy call).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clientId = $this->user('client')->client_id;
        $member = $this->member();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('client_users', 'email')->ignore($member)],
            'password' => [$member ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(ClientUserRole::class)],
            'branch_id' => [
                Rule::requiredIf($this->isMember()), 'nullable',
                Rule::exists('client_branches', 'id')->where('client_id', $clientId),
            ],
            'client_role_id' => [
                Rule::requiredIf($this->isMember()), 'nullable',
                Rule::exists('client_roles', 'id')->where('client_id', $clientId),
            ],
            'active' => ['boolean'],
        ];
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

            $losesOwner = $wasOwner && $member->active && ($this->isMember() || ! $this->boolean('active'));

            if ($losesOwner && $actor->client->users()->where('role', ClientUserRole::Owner)->where('active', true)->whereKeyNot($member->getKey())->doesntExist()) {
                $validator->errors()->add('role', __('portal.team.errors.last_owner'));
            }
        }];
    }

    public function isMember(): bool
    {
        return $this->input('role') === ClientUserRole::Member->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function memberData(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'email' => mb_strtolower($this->string('email')->trim()->value()),
            'role' => $this->enum('role', ClientUserRole::class),
            'branch_id' => $this->isMember() ? $this->integer('branch_id') : null,
            'client_role_id' => $this->isMember() ? $this->integer('client_role_id') : null,
            'active' => $this->boolean('active'),
            ...($this->filled('password') ? ['password' => $this->string('password')->value()] : []),
        ];
    }

    private function member(): ?ClientUser
    {
        return $this->route('teamMember');
    }
}
