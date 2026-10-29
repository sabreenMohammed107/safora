<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ClientStatus;
use App\Models\Client;
use Exception;

class InvalidClientStatusTransition extends Exception
{
    public static function make(Client $client, ClientStatus $to): self
    {
        return new self(sprintf(
            'Client %s cannot move from "%s" to "%s".',
            $client->code ?? $client->getKey(),
            $client->status->value,
            $to->value,
        ));
    }
}
