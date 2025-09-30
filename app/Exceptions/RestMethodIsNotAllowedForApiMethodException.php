<?php

namespace App\Exceptions;

use App\Enums\MethodEnum;

class RestMethodIsNotAllowedForApiMethodException extends \Exception
{

    public function __construct(
        public MethodEnum $methodEnum,
        public array      $allowedMethods = []
    )
    {
        parent::__construct();
    }
}
