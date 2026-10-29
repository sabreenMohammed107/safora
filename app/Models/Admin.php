<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdminPermission;
use App\Models\Concerns\ManagesPasswords;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * SAFORA staff account, authenticated through the "admin" guard. A super admin holds every
 * permission; other staff hold the permissions of their admin role. `is_super_admin`, `password`
 * flags and `must_change_password` are changed explicitly, never mass assigned from a form.
 */
#[Fillable(['name', 'title', 'email', 'password', 'locale', 'admin_role_id', 'active'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory, ManagesPasswords;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<AdminRole, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    public function hasPermission(AdminPermission $permission): bool
    {
        if (! $this->active) {
            return false;
        }

        return $this->is_super_admin || ($this->role?->grants($permission) ?? false);
    }

    #[Scope]
    protected function activeSuperAdmins(Builder $query): Builder
    {
        return $query->where('is_super_admin', true)->where('active', true);
    }
}
