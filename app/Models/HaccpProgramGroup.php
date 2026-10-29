<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * One of the 7 PRP groups (infrastructure, hygiene, people, equipment, supply, contamination, protection).
 */
#[Fillable(['key', 'label', 'icon', 'sort_order'])]
class HaccpProgramGroup extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['label'];

    /**
     * @return HasMany<HaccpProgram, $this>
     */
    public function programs(): HasMany
    {
        return $this->hasMany(HaccpProgram::class, 'group_id')->orderBy('sort_order');
    }
}
