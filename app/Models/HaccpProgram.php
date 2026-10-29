<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProgramStatus;
use Database\Factories\HaccpProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * A HACCP prerequisite program (PRP-01 … PRP-22 from the V17 library, plus programs the admin adds).
 * Every text field is bilingual; `checks` holds one review question per line.
 * Only programs added by the admin (`is_custom`) may be deleted (01_ADMIN.md §A).
 */
#[Fillable([
    'code', 'group_id', 'name', 'icon', 'summary', 'record_name', 'acceptance', 'limit_text', 'steps',
    'evidence', 'failure', 'haccp_link', 'responsible', 'frequency', 'checks', 'status', 'is_custom', 'sort_order',
])]
class HaccpProgram extends Model
{
    /** @use HasFactory<HaccpProgramFactory> */
    use HasFactory, HasTranslations;

    /**
     * Text fields edited in both languages, in display order.
     *
     * @var list<string>
     */
    public const array CONTENT_FIELDS = [
        'name', 'summary', 'record_name', 'responsible', 'frequency', 'acceptance',
        'limit_text', 'steps', 'evidence', 'failure', 'haccp_link', 'checks',
    ];

    /** @var list<string> */
    public array $translatable = self::CONTENT_FIELDS;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProgramStatus::class,
            'is_custom' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<HaccpProgramGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(HaccpProgramGroup::class, 'group_id');
    }

    /**
     * Content fields with Arabic text but no translation in the given locale
     * (06_I18N.md §6: untranslated content falls back to Arabic and is flagged).
     *
     * @return list<string>
     */
    public function missingTranslations(string $locale = 'en'): array
    {
        return array_values(array_filter(
            self::CONTENT_FIELDS,
            fn (string $field): bool => filled($this->getTranslation($field, 'ar', false))
                && blank($this->getTranslation($field, $locale, false)),
        ));
    }

    /**
     * @return list<string>
     */
    public function checkList(?string $locale = null): array
    {
        // "u" is required: without it \R also matches byte 0x85, which occurs inside UTF-8 Arabic characters.
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $this->getTranslation('checks', $locale ?? app()->getLocale())))));
    }

    public static function nextCode(): string
    {
        $highest = static::query()->pluck('code')->map(fn (string $code): int => (int) substr($code, 4))->max() ?? 0;

        return sprintf('PRP-%02d', $highest + 1);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', ProgramStatus::Active);
    }
}
