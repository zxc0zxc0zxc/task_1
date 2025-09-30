<?php
declare(strict_types=1);

namespace App\Dto\Convert;

use App\ValueObjects\AmountValueObject;
use App\ValueObjects\CurrencySymbolValueObject;

final readonly class ConvertResponseDto
{
    public function __construct(
        public CurrencySymbolValueObject $currencyFrom,
        public CurrencySymbolValueObject $currencyTo,
        public AmountValueObject         $value,
        public AmountValueObject         $convertedValue,
        public AmountValueObject         $rate,
    )
    {
    }
}
