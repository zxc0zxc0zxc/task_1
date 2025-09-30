<?php
declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Enums\SettingEnum;
use App\Exceptions\SettingIsNotSetException;

interface SettingRepositoryInterface
{
    /**
     * Получение настроек, тут комса 2% лежит
     * @param SettingEnum $settingEnum
     * @return string
     * @throws SettingIsNotSetException
     */
    public function getValue(SettingEnum $settingEnum): string;
}
