<?php
declare(strict_types=1);

namespace App\Interfaces\Services;

use App\Dto\Convert\ConvertDto;
use App\Dto\Convert\ConvertResponseDto;
use App\Exceptions\PairNotFoundException;
use App\Exceptions\SettingIsNotSetException;

interface ConvertServiceInterface
{
    /**
     * Обработка запроса сервисом
     * @param ConvertDto $convertDto
     * @return ConvertResponseDto
     * @throws PairNotFoundException
     * @throws SettingIsNotSetException
     */
    public function handle(ConvertDto $convertDto): ConvertResponseDto;
}
