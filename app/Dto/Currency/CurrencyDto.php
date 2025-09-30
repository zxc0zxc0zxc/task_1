<?php

namespace App\Dto\Currency;

use App\ValueObjects\CurrencySymbolValueObject;

final readonly class CurrencyDto
{
    public function __construct(
        public int                       $id,
        public CurrencySymbolValueObject $symbolValueObject
    )
    {
    }
}
