<?php
declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Dto\ExchangeRate\ExchangeRateDto;
use App\Dto\ExchangeRate\FillExchangeRateDto;
use App\ValueObjects\AmountValueObject;

interface ExchangeRateRepositoryInterface
{
    /**
     * Получение массива курсов валюты к другим по её id
     *
     * @param int $currencyId
     * @return array<ExchangeRateDto>
     */
    public function getExchangeRatesForCurrency(int $currencyId): array;

    /**
     * Получение курса обмена валют
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return AmountValueObject|null
     */
    public function findExchangeRateForPair(int $fromCurrencyId, int $toCurrencyId): ?AmountValueObject;

    /**
     * Обновление курсов
     *
     * @param array<FillExchangeRateDto> $data
     * @return void
     */
    public function fillRates(array $data): void;
}
