<?php

namespace App\Jobs;

use App\Dto\Currency\CurrencyDto;
use App\Dto\ExchangeRate\ExchangeRateResponseDto;
use App\Dto\ExchangeRate\FillExchangeRateDto;
use App\Interfaces\Pipelines\DataProviderPipelineInterface;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\Interfaces\Repositories\ExchangeRateRepositoryInterface;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Обновление курсов валют в фоне
 */
class UpdateExchangeRatesJob implements ShouldQueue
{
    use Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        CurrencyRepositoryInterface     $currencyRepository,
        ExchangeRateRepositoryInterface $exchangeRateRepository,
        DataProviderPipelineInterface   $dataProviderPipeline,
    ): void
    {
        $lock = Cache::lock('update-exchange-rates');

        if (!$lock->get()) {
            $this->release();
            return;
        }

        try {

            /** @var Collection<CurrencyDto> $currencies */
            $currencies = collect($currencyRepository->getAllCurrencies());

            $currencySymbolToIdMap = $currencies
                ->mapWithKeys(fn(CurrencyDto $currency) => [
                    $currency->symbolValueObject->getSymbol() => $currency->id
                ])
                ->all();

            while ($dataProviderPipeline->canNext()) {

                $dataProvider = $dataProviderPipeline->resolve();

                try {
                    $actualRates = $dataProvider->getRates();
                    break;

                } catch (Exception $exception) {

                    Log::channel('jobs')->error('Couldnt fetch exchange rates from data provider.', [
                        'from' => __CLASS__,
                        'providerClass' => class_basename($dataProvider),
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ]);

                    $dataProviderPipeline->next();

                }
            }

            if (!isset($actualRates)) {
                Log::channel('jobs')->critical('No data fetched from data providers.', [
                    'from' => __CLASS__,
                ]);

                $lock->release();

                $this->fail();
                return;
            }

            $mappedData = collect($actualRates)
                ->map(function (ExchangeRateResponseDto $rate) use ($currencySymbolToIdMap) {
                    $fromSymbol = $rate->fromCurrency->getSymbol();
                    $toSymbol = $rate->toCurrency->getSymbol();

                    // ненужные отметаем
                    if (!array_key_exists($fromSymbol, $currencySymbolToIdMap) || !array_key_exists($toSymbol, $currencySymbolToIdMap)) {
                        return null;
                    }

                    return new FillExchangeRateDto(
                        fromCurrencyId: $currencySymbolToIdMap[$fromSymbol],
                        toCurrencyId: $currencySymbolToIdMap[$toSymbol],
                        exchangeRate: $rate->rate
                    );
                })
                ->filter()
                ->values()
                ->all();

            $exchangeRateRepository->fillRates($mappedData);
        } finally {
            $lock->release();
        }
    }
}
