<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Origin of a sector a client has beyond its category's sectors.
 * Category sectors are never stored per client; they resolve from the category.
 */
enum SectorSource: string
{
    /** Granted manually by an admin. */
    case Granted = 'granted';

    /** Added by the client from the portal. */
    case Added = 'added';
}
