<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\ClientUserRole;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company', 'cr_number', 'contact_name', 'email', 'phone', 'sector_category_id',
    'status', 'status_reason', 'registered_at', 'approved_at',
])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Assign the public client code (CL-1001, CL-1002, ...) once the id is known.
     */
    protected static function booted(): void
    {
        static::created(function (Client $client): void {
            if ($client->code === null) {
                $client->forceFill(['code' => sprintf('CL-%d', 1000 + $client->id)])->saveQuietly();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'registered_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SectorCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SectorCategory::class, 'sector_category_id');
    }

    /**
     * Sectors granted by an admin or added by the client on top of the category's sectors.
     *
     * @return BelongsToMany<Sector, $this, ClientSector>
     */
    public function extraSectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class, 'client_sectors')
            ->using(ClientSector::class)
            ->withPivot('source')
            ->withTimestamps();
    }

    /**
     * All sectors the client may use: its category's sectors (resolved live, so category
     * edits apply immediately) plus its extra sectors.
     *
     * @return Builder<Sector>
     */
    public function allowedSectors(): Builder
    {
        return Sector::query()->where(function (Builder $query): void {
            $query->whereHas('categories', fn (Builder $category) => $category->whereKey($this->sector_category_id))
                ->orWhereHas('clients', fn (Builder $client) => $client->whereKey($this->getKey()));
        });
    }

    /**
     * @param  array<string, scalar|null>  $properties
     */
    public function logActivity(string $event, array $properties = [], ?Model $actor = null, ?string $actorName = null): ActivityLog
    {
        $log = new ActivityLog([
            'event' => $event,
            'properties' => $properties ?: null,
            'actor_name' => $actorName ?? $actor?->getAttribute('name') ?? 'system',
        ]);

        $log->actor()->associate($actor);

        return $this->activityLogs()->save($log);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * The client's current subscription (a client is on a single plan at a time).
     *
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany('start_date');
    }

    /**
     * @return HasMany<ClientBranch, $this>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(ClientBranch::class);
    }

    /**
     * @return HasMany<ClientUser, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * @return HasMany<ClientRole, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(ClientRole::class);
    }

    /**
     * The company manager account (the first owner created at registration).
     *
     * @return HasOne<ClientUser, $this>
     */
    public function owner(): HasOne
    {
        return $this->hasOne(ClientUser::class)->where('role', ClientUserRole::Owner)->oldestOfMany();
    }

    public function nextBranchCode(): string
    {
        $highest = $this->branches()->pluck('code')
            ->map(fn (string $code): int => (int) substr($code, 3))
            ->max() ?? 0;

        return sprintf('BR-%02d', $highest + 1);
    }

    /**
     * @return HasMany<ClientDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest('created_at');
    }

    #[Scope]
    protected function withStatus(Builder $query, ClientStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    #[Scope]
    protected function search(Builder $query, string $term): Builder
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(fn (Builder $inner) => $inner
            ->where('company', 'like', $like)
            ->orWhere('code', 'like', $like)
            ->orWhere('cr_number', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('contact_name', 'like', $like));
    }
}
