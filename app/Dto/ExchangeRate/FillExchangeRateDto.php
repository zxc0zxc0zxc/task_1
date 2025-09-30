<?php
declare(strict_types=1);

namespace App\Dto\ExchangeRate;

use App\ValueObjects\AmountValueObject;

final readonly class FillExchangeRateDto
{
    public function __construct(
        public int $fromCurrencyId,
        public int $toCurrencyId,
        public AmountValueObject $exchangeRate
    )
    {
    }
}
