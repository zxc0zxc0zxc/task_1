<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

readonly class CurrencySymbolRule implements ValidationRule
{
    /**
     * Валидация символа валюты - 3 буквы верхним регистром
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^[A-Z]{3,5}$/', $value)) {
            $fail("The :attribute must be a valid currency symbol");
        }
    }
}
