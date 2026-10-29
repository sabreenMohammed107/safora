<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SectorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['key', 'name', 'active'])]
class Sector extends Model
{
    /** @use HasFactory<SectorFactory> */
    use HasFactory, HasTranslations;

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
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<SectorCategory, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(SectorCategory::class, 'sector_category_sector');
    }

    /**
     * Clients that hold this sector as an extra (granted or added) sector.
     *
     * @return BelongsToMany<Client, $this, ClientSector>
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_sectors')
            ->using(ClientSector::class)
            ->withPivot('source')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
