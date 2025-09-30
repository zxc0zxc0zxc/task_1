<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Dto\ExchangeRate\ExchangeRateDto;
use App\Interfaces\Repositories\ExchangeRateRepositoryInterface;
use App\Models\ExchangeRate;
use App\ValueObjects\AmountValueObject;
use App\ValueObjects\CurrencySymbolValueObject;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class ExchangeRateRepository implements ExchangeRateRepositoryInterface
{

    /** @inheritDoc */
    public function getExchangeRatesForCurrency(int $currencyId): array
    {
        $data = ExchangeRate::query()
            ->select(['currencies.symbol', 'exchange_rates.rate'])
            ->join('currencies', 'exchange_rates.to_currency_id', '=', 'currencies.id')
            ->where('exchange_rates.from_currency_id', '=', $currencyId)
            ->get()
            ->all();

        $mapped = Arr::map($data, static function (ExchangeRate $result) {
            return new ExchangeRateDto(
                toCurrency: new CurrencySymbolValueObject((string)$result->symbol),
                rate: new AmountValueObject((string)$result->rate)
            );
        });

        usort($mapped, static function (ExchangeRateDto $dto1, ExchangeRateDto $dto2) {
            return bccomp($dto1->rate->getAmount(), $dto2->rate->getAmount(), 10);
        });

        return $mapped;
    }

    /** @inheritDoc */
    public function findExchangeRateForPair(int $fromCurrencyId, int $toCurrencyId): ?AmountValueObject
    {
        $rate = ExchangeRate::query()
            ->select(['exchange_rates.rate'])
            ->where('exchange_rates.from_currency_id', '=', $fromCurrencyId)
            ->where('exchange_rates.to_currency_id', '=', $toCurrencyId)
            ->value('rate');


        return new AmountValueObject((string)$rate);
    }

    /** @inheritDoc */
    public function fillRates(array $data): void
    {
        if (empty($data)) {
            return;
        }

        DB::transaction(static function () use ($data) {
            foreach ($data as $rate) {
                ExchangeRate::updateOrCreate(
                    [
                        'from_currency_id' => $rate->fromCurrencyId,
                        'to_currency_id' => $rate->toCurrencyId
                    ],
                    [
                        'rate' => $rate->exchangeRate->getAmount()
                    ]
                );
            }
        });
    }
}
