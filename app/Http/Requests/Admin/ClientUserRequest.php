<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\ClientUserRequest as BaseClientUserRequest;
use App\Models\Client;
use App\Models\ClientUser;

/**
 * A client portal account edited by SAFORA staff from the client profile.
 * Leaving the password empty on create generates a temporary one.
 */
class ClientUserRequest extends BaseClientUserRequest
{
    protected function client(): Client
    {
        return $this->route('client');
    }

    protected function member(): ?ClientUser
    {
        return $this->route('user');
    }

    protected function passwordRequiredOnCreate(): bool
    {
        return false;
    }
}
