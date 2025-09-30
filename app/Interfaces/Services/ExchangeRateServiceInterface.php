<?php
declare(strict_types=1);

namespace App\Interfaces\Services;

use App\Dto\ExchangeRate\ExchangeRateDto;
use App\Exceptions\SettingIsNotSetException;
use App\ValueObjects\CurrencySymbolValueObject;

interface ExchangeRateServiceInterface
{
    /**
     * Обработка запроса сервисом
     * @param CurrencySymbolValueObject $symbol
     * @return array<ExchangeRateDto>
     * @throws SettingIsNotSetException
     */
    public function handle(CurrencySymbolValueObject $symbol): array;
}
