<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * A signed-in admin or client user choosing their own password (current password required).
 * The guard is taken from the route: admin.* routes use "admin", portal.* routes use "client".
 */
class UpdateOwnPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'current_password' => ['required', 'current_password:'.$this->guardName()],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ];
    }

    public function guardName(): string
    {
        return $this->routeIs('admin.*') ? 'admin' : 'client';
    }

    public function newPassword(): string
    {
        return $this->string('password')->value();
    }
}
