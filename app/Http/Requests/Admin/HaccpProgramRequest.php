<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ProgramStatus;
use App\Models\HaccpProgram;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A PRP program's content in Arabic (required name) and English (optional — untranslated
 * fields fall back to Arabic and are flagged in the command center).
 */
class HaccpProgramRequest extends FormRequest
{
    public const int MAX_TEXT = 5000;

    /**
     * Determine if the user is authorized to make this request (route middleware: can:haccp.manage).
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
        $rules = [
            'group_id' => ['required', Rule::exists('haccp_program_groups', 'id')],
            'icon' => ['required', 'string', 'max:64', 'regex:/^fa-[a-z0-9-]+$/'],
            'status' => ['required', Rule::enum(ProgramStatus::class)],
            'name.ar' => ['required', 'string', 'max:120'],
            'name.en' => ['nullable', 'string', 'max:120'],
        ];

        foreach (array_diff(HaccpProgram::CONTENT_FIELDS, ['name']) as $field) {
            $rules[$field.'.ar'] = ['nullable', 'string', 'max:'.self::MAX_TEXT];
            $rules[$field.'.en'] = ['nullable', 'string', 'max:'.self::MAX_TEXT];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function programData(): array
    {
        $data = [
            'group_id' => $this->integer('group_id'),
            'icon' => $this->string('icon')->value(),
            'status' => $this->enum('status', ProgramStatus::class),
        ];

        foreach (HaccpProgram::CONTENT_FIELDS as $field) {
            $data[$field] = array_filter(
                ['ar' => trim((string) $this->input($field.'.ar')), 'en' => trim((string) $this->input($field.'.en'))],
                fn (string $text): bool => $text !== '',
            );
        }

        return $data;
    }
}
