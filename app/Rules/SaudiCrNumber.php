<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * A Saudi commercial registration number (10 digits) or VAT number (15 digits starting and ending with 3).
 */
class SaudiCrNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^(\d{10}|3\d{13}3)$/', $value)) {
            $fail('clients.validation.cr_number')->translate();
        }
    }
}
