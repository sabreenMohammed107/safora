<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update a staff role. Staff may only grant permissions they hold themselves.
 */
class AdminRoleRequest extends FormRequest
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
        /** @var AdminRole|null $role */
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('admin_roles', 'name')->ignore($role)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(array_map(fn (AdminPermission $permission): string => $permission->value, self::grantableBy($this->user('admin'))))],
        ];
    }

    /**
     * @return array{name: string, description: ?string, permissions: list<AdminPermission>}
     */
    public function roleData(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'description' => $this->filled('description') ? $this->string('description')->trim()->value() : null,
            'permissions' => array_map(AdminPermission::from(...), array_values(array_unique($this->input('permissions', [])))),
        ];
    }

    /**
     * @return list<AdminPermission>
     */
    public static function grantableBy(Admin $actor): array
    {
        return array_values(array_filter(AdminPermission::cases(), $actor->hasPermission(...)));
    }
}
