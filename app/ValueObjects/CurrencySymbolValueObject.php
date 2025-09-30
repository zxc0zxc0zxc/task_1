<?php
declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class CurrencySymbolValueObject
{
    private string $symbol;

    public function __construct(string $symbol)
    {
        if (trim($symbol) === '') {
            throw new InvalidArgumentException('Currency symbol is empty');
        }

        $this->symbol = strtoupper($symbol);
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }
}
