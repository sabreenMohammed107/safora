<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Branch details, shared by the admin client profile and the portal. Capacity and
 * "last active branch" rules are enforced by ClientAccountService.
 */
class BranchRequest extends FormRequest
{
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
        return [
            'name' => ['required', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'manager' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'active' => ['boolean'],
        ];
    }

    /**
     * @return array{name: string, city: ?string, address: ?string, manager: ?string, phone: ?string, active: bool}
     */
    public function branchData(): array
    {
        return [
            ...$this->safe()->only(['name', 'city', 'address', 'manager', 'phone']),
            'active' => $this->boolean('active'),
        ];
    }
}
