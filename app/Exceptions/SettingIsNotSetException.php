<?php

namespace App\Exceptions;

use App\Enums\SettingEnum;

/**
 * Выбрасывается, когда не указана какая-либо из настроек
 */
class SettingIsNotSetException extends \Exception
{
    public function __construct(SettingEnum $settingEnum)
    {
        parent::__construct("The setting {$settingEnum->value} is not set");
    }
}
