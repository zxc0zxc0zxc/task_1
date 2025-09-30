<?php
declare(strict_types=1);

namespace App\ValueObjects;

use App\Helpers\AmountHelper;
use InvalidArgumentException;

final class AmountValueObject
{
    private string $amount;

    public function __construct(string $amount)
    {
        if (
            bccomp('0', $amount, 16) === 1
        ) {
            throw new InvalidArgumentException('Amount cant be lower than 0');
        }

        $this->amount = $amount;
    }

    public function getAmount(): string
    {
        return bccomp('1', $this->amount, 10) === 1 ?
            AmountHelper::format($this->amount, 10) :
            AmountHelper::format($this->amount, 2);
    }

    public function multiply(string $amount): self
    {
        if (
            bccomp('0', $amount, 10) === 1
        ) {
            throw new InvalidArgumentException('Amount cant be lower than 0');
        }

        $this->amount = bcmul($amount, $this->amount, 10);
        return $this;
    }
}
