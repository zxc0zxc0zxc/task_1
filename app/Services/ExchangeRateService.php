<?php
declare(strict_types=1);

namespace App\Services;

use App\Dto\ExchangeRate\ExchangeRateDto;
use App\Enums\SettingEnum;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\Interfaces\Repositories\ExchangeRateRepositoryInterface;
use App\Interfaces\Repositories\SettingRepositoryInterface;
use App\Interfaces\Services\ExchangeRateServiceInterface;
use App\ValueObjects\CurrencySymbolValueObject;
use Illuminate\Support\Collection;

final readonly class ExchangeRateService implements ExchangeRateServiceInterface
{
    public function __construct(
        private CurrencyRepositoryInterface     $currencyRepository,
        private ExchangeRateRepositoryInterface $exchangeRateRepository,
        private SettingRepositoryInterface      $settingRepository,
    )
    {
    }

    /** @inheritDoc */
    public function handle(CurrencySymbolValueObject $symbol): array
    {
        $currency = $this->currencyRepository->getCurrency($symbol);

        $exchangeRates = $this->exchangeRateRepository->getExchangeRatesForCurrency($currency->id);

        $fee = $this->settingRepository->getValue(SettingEnum::FEE_PARAM);

        return $this->formatExchangeRates($exchangeRates, $fee);
    }

    /**
     * Форматирование курсов
     * @param Collection|array $exchangeRates
     * @param string $fee
     * @return array
     */
    private function formatExchangeRates(Collection|array $exchangeRates, string $fee): array
    {
        if (is_array($exchangeRates)) {
            $exchangeRates = collect($exchangeRates);
        }

        return collect($exchangeRates)
            ->mapWithKeys(fn(ExchangeRateDto $dto) => [
                $dto->toCurrency->getSymbol() => $dto
                    ->rate
                    ->multiply($fee)
                    ->getAmount()
            ])
            ->all();
    }
}
