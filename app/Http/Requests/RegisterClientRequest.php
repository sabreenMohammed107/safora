<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\SaudiCrNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Public self-registration of a new client company (lands as "pending" for admin review).
 * The email and password become the company manager's (owner's) portal login.
 */
class RegisterClientRequest extends FormRequest
{
    public const int MAX_DOCUMENTS = 10;

    public const int MAX_DOCUMENT_KB = 5120;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cr_number' => preg_replace('/\s+/', '', (string) $this->input('cr_number')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'sector_category_id' => ['required', Rule::exists('sector_categories', 'id')->where('active', true)],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255', Rule::unique('client_users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'cr_number' => ['required', new SaudiCrNumber],
            'documents' => ['required', 'array', 'max:'.self::MAX_DOCUMENTS],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:'.self::MAX_DOCUMENT_KB],
        ];
    }

    /**
     * @return array{company: string, sector_category_id: int|string, contact_name: string, phone: string, email: string, cr_number: string}
     */
    public function companyData(): array
    {
        return $this->safe()->only(['company', 'sector_category_id', 'contact_name', 'phone', 'email', 'cr_number']);
    }

    /**
     * @return list<UploadedFile>
     */
    public function documents(): array
    {
        return array_values($this->file('documents', []));
    }
}
