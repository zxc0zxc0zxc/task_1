<?php
declare(strict_types=1);

namespace App\Dto\ExchangeRate;

use App\ValueObjects\AmountValueObject;
use App\ValueObjects\CurrencySymbolValueObject;

final readonly class ExchangeRateResponseDto
{
    public function __construct(
        public CurrencySymbolValueObject $fromCurrency,
        public CurrencySymbolValueObject $toCurrency,
        public AmountValueObject         $rate
    )
    {
    }
}
