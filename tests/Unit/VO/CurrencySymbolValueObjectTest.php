<?php

namespace Tests\Unit\VO;

use App\ValueObjects\CurrencySymbolValueObject;
use Tests\TestCase;

class CurrencySymbolValueObjectTest extends TestCase
{
    public function testCantMakeEmpty(): void
    {
        $this->assertThrows(function () {
            new CurrencySymbolValueObject('    ');
        }, \InvalidArgumentException::class);
    }

    public function testReturnsCapitalSymbol(): void
    {
        $symbol = new CurrencySymbolValueObject('usd');

        $this->assertEquals('USD', $symbol->getSymbol());
    }
}
