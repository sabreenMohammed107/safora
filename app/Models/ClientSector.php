<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SectorSource;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClientSector extends Pivot
{
    protected $table = 'client_sectors';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => SectorSource::class,
        ];
    }
}
