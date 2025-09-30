<?php
declare(strict_types=1);

namespace App\Dto\ExchangeRate;

use App\ValueObjects\AmountValueObject;
use App\ValueObjects\CurrencySymbolValueObject;

final readonly class ExchangeRateDto
{
    public function __construct(
        public CurrencySymbolValueObject $toCurrency,
        public AmountValueObject         $rate
    )
    {
    }
}
