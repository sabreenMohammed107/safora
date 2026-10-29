<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SectorSource;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Extra sectors on top of a client's category sectors (prototype: grantSector / revokeSector in
 * 11_ClientCenter.html). Category sectors are inherited and cannot be revoked here.
 */
class ClientSectorService
{
    /**
     * Sectors the admin can still grant: active, not already allowed, and with at least one product.
     *
     * @return Collection<int, Sector>
     */
    public function grantable(Client $client): Collection
    {
        $allowedIds = $client->allowedSectors()->pluck('id');

        return Sector::query()
            ->where('active', true)
            ->whereNotIn('id', $allowedIds)
            ->whereHas('products', fn (Builder $query) => $query->where('active', true))
            ->withCount(['products' => fn (Builder $query) => $query->where('active', true)])
            ->get()
            ->sortBy(fn (Sector $sector): string => $sector->name)
            ->values();
    }

    public function grant(Client $client, Sector $sector, ?Model $actor = null): void
    {
        if ($client->allowedSectors()->whereKey($sector->getKey())->exists()) {
            return;
        }

        $client->extraSectors()->attach($sector, ['source' => SectorSource::Granted]);
        $client->logActivity('sector_granted', ['name' => $sector->name], $actor);
    }

    /**
     * Removes a granted or client-added sector and the client's branch products that are no longer
     * within an allowed sector. Returns how many branch products were removed.
     */
    public function revoke(Client $client, Sector $sector, ?Model $actor = null): int
    {
        return DB::transaction(function () use ($client, $sector, $actor): int {
            $client->extraSectors()->detach($sector);

            $allowedIds = $client->allowedSectors()->pluck('id');

            $removed = DB::table('branch_products')
                ->whereIn('branch_id', $client->branches()->select('id'))
                ->whereIn('product_id', Product::query()->whereNotIn('sector_id', $allowedIds)->select('id'))
                ->delete();

            $client->logActivity($removed > 0 ? 'sector_revoked_with_products' : 'sector_revoked', [
                'name' => $sector->name,
                'count' => $removed,
            ], $actor);

            return $removed;
        });
    }

    /**
     * Distinct products the client has chosen across its branches.
     */
    public function productCount(Client $client): int
    {
        return DB::table('branch_products')
            ->whereIn('branch_id', $client->branches()->select('id'))
            ->distinct()
            ->count('product_id');
    }
}
