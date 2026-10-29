<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientPermission;
use Database\Factories\ClientRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A role a company manager defines for their employees. Default roles carry a `key`
 * (shown with a translated name until renamed); custom roles keep the name as written.
 *
 * @property Collection<int, ClientPermission> $permissions
 */
#[Fillable(['client_id', 'key', 'name', 'description', 'permissions'])]
class ClientRole extends Model
{
    /** @use HasFactory<ClientRoleFactory> */
    use HasFactory;

    /**
     * Default roles created for every client (docs/sprints/03_BUSINESS_RULES.md §3).
     *
     * @var array<string, list<ClientPermission>>
     */
    public const array DEFAULTS = [
        'branch_manager' => [
            ClientPermission::RecordsView, ClientPermission::RecordsCreate, ClientPermission::RecordsEdit, ClientPermission::RecordsDelete,
            ClientPermission::PrpAccess, ClientPermission::HaccpPlansManage, ClientPermission::ControlPointsMonitor,
            ClientPermission::CriticalLimitsRecord, ClientPermission::TraceabilityManage, ClientPermission::IsoAccess,
            ClientPermission::ProductsManage, ClientPermission::ProductRequestsCreate,
        ],
        'branch_user' => [
            ClientPermission::RecordsView, ClientPermission::RecordsCreate, ClientPermission::RecordsEdit,
            ClientPermission::PrpAccess, ClientPermission::ControlPointsMonitor, ClientPermission::CriticalLimitsRecord,
            ClientPermission::IsoAccess, ClientPermission::ProductRequestsCreate,
        ],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => AsEnumCollection::of(ClientPermission::class),
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<ClientUser, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * Default roles first, then custom roles by name.
     *
     * @param  Collection<int, self>  $roles
     * @return Collection<int, self>
     */
    public static function sortForDisplay(Collection $roles): Collection
    {
        return $roles->sortBy(fn (self $role): array => [$role->key === null ? 1 : 0, $role->displayName()])->values();
    }

    public function displayName(): string
    {
        return $this->key !== null ? __('portal.default_roles.'.$this->key) : $this->name;
    }

    public function grants(ClientPermission $permission): bool
    {
        return $this->permissions->contains($permission);
    }

    /**
     * Creates the default roles for a new client.
     *
     * @return array<string, self> keyed by role key
     */
    public static function createDefaultsFor(Client $client): array
    {
        $roles = [];

        foreach (self::DEFAULTS as $key => $permissions) {
            $roles[$key] = $client->roles()->firstOrCreate(['key' => $key], [
                'name' => __('portal.default_roles.'.$key),
                'permissions' => $permissions,
            ]);
        }

        return $roles;
    }
}
