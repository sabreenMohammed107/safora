<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientPermission;
use App\Enums\ClientStatus;
use App\Enums\ClientUserRole;
use App\Enums\PortalBlock;
use Database\Factories\ClientUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A client company's portal account, authenticated through the "client" guard.
 * Owners have no branch_id and hold every permission their plan allows; members belong to
 * exactly one branch and hold the permissions of their client role (also bounded by the plan).
 */
#[Fillable(['client_id', 'branch_id', 'client_role_id', 'name', 'email', 'role', 'password', 'locale', 'active'])]
#[Hidden(['password', 'remember_token'])]
class ClientUser extends Authenticatable
{
    /** @use HasFactory<ClientUserFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ClientUserRole::class,
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
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
     * @return BelongsTo<ClientBranch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(ClientBranch::class, 'branch_id');
    }

    /**
     * @return BelongsTo<ClientRole, $this>
     */
    public function clientRole(): BelongsTo
    {
        return $this->belongsTo(ClientRole::class);
    }

    public function isOwner(): bool
    {
        return $this->role === ClientUserRole::Owner;
    }

    public function roleLabel(): string
    {
        return $this->isOwner() ? $this->role->label() : ($this->clientRole?->displayName() ?? $this->role->label());
    }

    public function hasPermission(ClientPermission $permission): bool
    {
        if (! $permission->isAvailableOn($this->client->subscription?->plan)) {
            return false;
        }

        return $this->isOwner() || ($this->clientRole?->grants($permission) ?? false);
    }

    /**
     * Every permission this user effectively holds right now.
     *
     * @return list<ClientPermission>
     */
    public function permissions(): array
    {
        return array_values(array_filter(ClientPermission::cases(), $this->hasPermission(...)));
    }

    /**
     * Active branches this user may open: all of the company's for an owner, otherwise only their own.
     *
     * @return HasMany<ClientBranch, Client>
     */
    public function accessibleBranches(): HasMany
    {
        $branches = $this->client->branches()->active()->orderBy('code');

        return $this->isOwner() ? $branches : $branches->whereKey($this->branch_id);
    }

    /**
     * Why the portal is closed to this user, or null when it is open.
     * An admin impersonating the owner bypasses the user's own inactive flag only.
     */
    public function portalBlock(bool $impersonated = false): ?PortalBlock
    {
        $client = $this->client;

        $statusBlock = match ($client->status) {
            ClientStatus::Pending => PortalBlock::Pending,
            ClientStatus::Rejected => PortalBlock::Rejected,
            ClientStatus::Suspended => PortalBlock::Suspended,
            ClientStatus::Active => null,
        };

        return match (true) {
            $statusBlock !== null => $statusBlock,
            $client->subscription === null => PortalBlock::NoPlan,
            $client->subscription->isExpired() => PortalBlock::Expired,
            ! $impersonated && ! $this->active => PortalBlock::UserInactive,
            ! $this->accessibleBranches()->exists() => PortalBlock::NoBranch,
            default => null,
        };
    }
}
