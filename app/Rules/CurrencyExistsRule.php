<?php

namespace App\Rules;

use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

readonly class CurrencyExistsRule implements ValidationRule
{
    public function __construct(
        private CurrencyRepositoryInterface $currencyRepository
    )
    {
    }

    /**
     * Доп валидация репозиторием
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->currencyRepository->existsBySymbol($value)) {
            $fail("The :attribute $value is not supported.");
        }
    }
}
