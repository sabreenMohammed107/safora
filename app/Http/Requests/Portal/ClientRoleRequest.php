<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal;

use App\Enums\ClientPermission;
use App\Models\ClientRole;
use App\Models\ClientUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update a company role. Grantable permissions are bounded by the client's plan
 * and by the signed-in user's own permissions.
 */
class ClientRoleRequest extends FormRequest
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
        $actor = $this->user('client');
        /** @var ClientRole|null $role */
        $role = $this->route('teamRole');

        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('client_roles', 'name')->where('client_id', $actor->client_id)->ignore($role)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(array_map(fn (ClientPermission $permission): string => $permission->value, self::grantableBy($actor)))],
        ];
    }

    /**
     * @return array{name: string, description: ?string, permissions: list<ClientPermission>}
     */
    public function roleData(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'description' => $this->filled('description') ? $this->string('description')->trim()->value() : null,
            'permissions' => array_map(ClientPermission::from(...), array_values(array_unique($this->input('permissions', [])))),
        ];
    }

    /**
     * Permissions the user may hand out: available on the plan and held by the user.
     *
     * @return list<ClientPermission>
     */
    public static function grantableBy(ClientUser $actor): array
    {
        return $actor->permissions();
    }
}
