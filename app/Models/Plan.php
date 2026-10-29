<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanModule as Module;
use App\Enums\PlanTier;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'code', 'name', 'tier', 'price_monthly', 'price_annual', 'included_branches',
    'extra_branch_monthly', 'extra_branch_annual', 'max_sites', 'max_users',
    'includes_plan_id', 'active',
])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasTranslations;

    public const string HACCP = 'PLN-HACCP';

    public const string ISO = 'PLN-ISO';

    /** @var list<string> */
    public array $translatable = ['name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => PlanTier::class,
            'price_monthly' => 'decimal:2',
            'price_annual' => 'decimal:2',
            'included_branches' => 'integer',
            'extra_branch_monthly' => 'decimal:2',
            'extra_branch_annual' => 'decimal:2',
            'max_sites' => 'integer',
            'max_users' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PlanModule, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(PlanModule::class);
    }

    /**
     * The plan whose modules are always bundled into this one (ISO includes HACCP).
     *
     * @return BelongsTo<Plan, $this>
     */
    public function includedPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'includes_plan_id');
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasModule(Module $module): bool
    {
        return $this->modules->contains('module_key', $module)
            || ($this->includedPlan?->hasModule($module) ?? false);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
