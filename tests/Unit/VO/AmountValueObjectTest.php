<?php

namespace Tests\Unit\VO;

use App\ValueObjects\AmountValueObject;
use Tests\TestCase;

class AmountValueObjectTest extends TestCase
{
    public function testCantMakeNegative(): void
    {
        $this->assertThrows(function () {
            new AmountValueObject('-1');
        }, \InvalidArgumentException::class);
    }

    public function testMultiplies(): void
    {
        $num = '1';
        $mul = '2';

        $amount = new AmountValueObject($num);
        $amount->multiply($mul);

        $this->assertEquals(
            bcmul($num, $mul), $amount->getAmount()
        );
    }
    public function testCantMultiplyNegative(): void
    {
        $this->assertThrows(function () {

            $num = '1';
            $mul = '-1';
            $amount = new AmountValueObject($num);
            $amount->multiply($mul);
        }, \InvalidArgumentException::class);
    }
}
