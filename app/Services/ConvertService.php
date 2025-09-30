<?php
declare(strict_types=1);

namespace App\Services;

use App\Dto\Convert\ConvertDto;
use App\Dto\Convert\ConvertResponseDto;
use App\Enums\SettingEnum;
use App\Exceptions\PairNotFoundException;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\Interfaces\Repositories\ExchangeRateRepositoryInterface;
use App\Interfaces\Repositories\SettingRepositoryInterface;
use App\Interfaces\Services\ConvertServiceInterface;
use App\ValueObjects\AmountValueObject;

final readonly class ConvertService implements ConvertServiceInterface
{
    public function __construct(
        private CurrencyRepositoryInterface     $currencyRepository,
        private ExchangeRateRepositoryInterface $exchangeRateRepository,
        private SettingRepositoryInterface      $settingRepository
    )
    {
    }

    /** @inheritDoc */
    public function handle(ConvertDto $convertDto): ConvertResponseDto
    {

        $currencyFrom = $this->currencyRepository->getCurrency($convertDto->currencyFrom);
        $currencyTo = $this->currencyRepository->getCurrency($convertDto->currencyTo);

        $exchangeRate = $this->exchangeRateRepository->findExchangeRateForPair(
            fromCurrencyId: $currencyFrom->id,
            toCurrencyId: $currencyTo->id
        );

        if (!$exchangeRate) {
            throw new PairNotFoundException($convertDto->currencyFrom->getSymbol(), $convertDto->currencyTo->getSymbol());
        }

        $fee = $this->settingRepository->getValue(SettingEnum::FEE_PARAM);

        return new ConvertResponseDto(
            currencyFrom: $convertDto->currencyFrom,
            currencyTo: $convertDto->currencyTo,
            value: $convertDto->value,
            convertedValue: $this->convertWithFee(clone $convertDto->value, $exchangeRate, $fee),
            rate: $exchangeRate
        );

    }

    /**
     * Конвертация с начислением комиссии
     * @param AmountValueObject $value сумма
     * @param AmountValueObject $rate курс
     * @param string $fee комса
     * @return AmountValueObject
     */
    private function convertWithFee(AmountValueObject $value, AmountValueObject $rate, string $fee): AmountValueObject
    {
        return $value->multiply(
            $rate->multiply($fee)->getAmount()
        );
    }
}
