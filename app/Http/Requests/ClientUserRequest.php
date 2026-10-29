<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ClientUserRole;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * A client portal account, as edited from the admin client profile or the portal team page.
 * Members need a branch and a role of the same company; emails are unique across every client.
 * "Last active owner" and plan-capacity rules are enforced by ClientAccountService.
 */
abstract class ClientUserRequest extends FormRequest
{
    abstract protected function client(): Client;

    abstract protected function member(): ?ClientUser;

    /**
     * Whether a new account must be given a password (otherwise one is generated).
     */
    abstract protected function passwordRequiredOnCreate(): bool;

    /**
     * Determine if the user is authorized to make this request (checked by route middleware/policies).
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
        $clientId = $this->client()->getKey();
        $member = $this->member();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('client_users', 'email')->ignore($member)],
            'password' => [! $member && $this->passwordRequiredOnCreate() ? 'required' : 'nullable', 'confirmed', Password::defaults()],
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

    public function isMember(): bool
    {
        return $this->input('role') === ClientUserRole::Member->value;
    }

    /**
     * @return array{name: string, email: string, role: ClientUserRole, branch_id: ?int, client_role_id: ?int, active: bool, password?: string}
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
}
