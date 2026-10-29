<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdminPermission;
use Database\Factories\AdminRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A staff role defined by a super admin (or staff with roles.manage).
 *
 * @property Collection<int, AdminPermission> $permissions
 */
#[Fillable(['name', 'description', 'permissions'])]
class AdminRole extends Model
{
    /** @use HasFactory<AdminRoleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => AsEnumCollection::of(AdminPermission::class),
        ];
    }

    /**
     * @return HasMany<Admin, $this>
     */
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function grants(AdminPermission $permission): bool
    {
        return $this->permissions->contains($permission);
    }
}
