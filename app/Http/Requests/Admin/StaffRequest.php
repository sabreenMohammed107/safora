<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Create or update a SAFORA staff account. A password left empty on create is generated;
 * passwords set here are temporary (changed at next sign-in) unless staff edit themselves.
 * Rules: a non-super admin needs a role; only a super admin may grant or revoke super admin;
 * nobody may deactivate or demote themselves; at least one active super admin must remain.
 */
class StaffRequest extends FormRequest
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
        $staff = $this->staff();

        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($staff)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'locale' => ['required', Rule::in(array_keys(config('safora.locales')))],
            'is_super_admin' => ['boolean'],
            'admin_role_id' => [Rule::requiredIf(! $this->wantsSuperAdmin()), 'nullable', Rule::exists('admin_roles', 'id')],
            'active' => ['boolean'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $actor = $this->user('admin');
            $staff = $this->staff();
            $wasSuperAdmin = (bool) $staff?->is_super_admin;

            if ($this->wantsSuperAdmin() !== $wasSuperAdmin && Gate::forUser($actor)->denies('grantSuperAdmin', Admin::class)) {
                $validator->errors()->add('is_super_admin', __('admin.staff.errors.super_admin_only'));
            }

            if ($staff?->is($actor) && (! $this->boolean('active') || ($wasSuperAdmin && ! $this->wantsSuperAdmin()))) {
                $validator->errors()->add('active', __('admin.staff.errors.self_lockout'));
            }

            $losesSuperAdmin = $wasSuperAdmin && $staff->active && (! $this->wantsSuperAdmin() || ! $this->boolean('active'));

            if ($losesSuperAdmin && Admin::query()->activeSuperAdmins()->whereKeyNot($staff->getKey())->doesntExist()) {
                $validator->errors()->add('is_super_admin', __('admin.staff.errors.last_super_admin'));
            }
        }];
    }

    public function wantsSuperAdmin(): bool
    {
        return $this->boolean('is_super_admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function staffData(): array
    {
        return [
            ...$this->safe()->only(['name', 'title', 'email', 'locale']),
            'admin_role_id' => $this->wantsSuperAdmin() ? null : $this->integer('admin_role_id'),
            'active' => $this->boolean('active'),
        ];
    }

    public function newPassword(): ?string
    {
        return $this->filled('password') ? $this->string('password')->value() : null;
    }

    private function staff(): ?Admin
    {
        return $this->route('staff');
    }
}
