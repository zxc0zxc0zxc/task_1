<?php

namespace App\Exceptions;

/**
 * Выбрасывается, когда нет пары
 */
class PairNotFoundException extends \Exception
{
    public function __construct(string $symbolBase, string $symbolQuote)
    {
        parent::__construct("The pair $symbolBase/$symbolQuote can't be converted.");
    }
}
