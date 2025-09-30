<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

readonly class AmountRule implements ValidationRule
{
    /**
     * Валидация суммы
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^\d{1,36}(\.\d{1,10})?$/', $value)) {
            $fail("The :attribute is too great or too low");
            return;
        }

        if (bccomp($value, '0', 8) <= 0) {
            $fail("The :attribute is negative");
        }
    }
}
