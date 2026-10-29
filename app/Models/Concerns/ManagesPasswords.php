<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Password changes for Admin and ClientUser. Every change rotates the "remember me" token so
 * remembered devices are signed out; other active sessions are ended by the auth.session middleware.
 */
trait ManagesPasswords
{
    /**
     * A password chosen by an administrator or company manager: the owner must replace it at next sign-in.
     */
    public function assignTemporaryPassword(string $password): static
    {
        return $this->changePassword($password, mustChange: true);
    }

    /**
     * A password the account holder chose themselves.
     */
    public function assignOwnPassword(string $password): static
    {
        return $this->changePassword($password, mustChange: false);
    }

    /**
     * A random password that satisfies Password::defaults() (letters and numbers).
     */
    public static function generatePassword(): string
    {
        return Str::password(12, symbols: false);
    }

    private function changePassword(string $password, bool $mustChange): static
    {
        $this->password = $password;
        $this->must_change_password = $mustChange;
        $this->setRememberToken(Str::random(60));

        return $this;
    }
}
