<?php

namespace App\Jobs;

use App\Dto\ExchangeRate\ExchangeRateResponseDto;
use App\Interfaces\Pipelines\DataProviderPipelineInterface;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Обновление списка монет в фоне
 */
class UpdateCurrenciesTableJob implements ShouldQueue
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
        DataProviderPipelineInterface $dataProviderPipeline,
        CurrencyRepositoryInterface   $currencyRepository
    ): void
    {
        $lock = Cache::lock('update-exchange-rates');

        if (!$lock->get()) {
            $this->release();
            return;
        }

        try {
            $symbols = collect();

            while ($dataProviderPipeline->canNext()) {

                $dataProvider = $dataProviderPipeline->resolve();

                try {
                    $fromExchangeRates = $dataProvider->getRates();
                    break;

                } catch (Exception $exception) {

                    Log::channel('jobs')->error('Couldnt fetch currency list from data provider.', [
                        'from' => __CLASS__,
                        'providerClass' => class_basename($dataProvider),
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ]);

                    $dataProviderPipeline->next();

                }
            }

            if (!isset($fromExchangeRates)) {
                Log::channel('jobs')->critical('No data fetched from data providers.', [
                    'from' => __CLASS__,
                ]);
                $lock->release();

                $this->fail();
                return;
            }

            collect($fromExchangeRates)->flatMap(function (ExchangeRateResponseDto $fromExchangeRate) use (&$symbols) {
                $symbols->add($fromExchangeRate->fromCurrency);
                $symbols->add($fromExchangeRate->toCurrency);
            });

            $currencyRepository->fill(
                $symbols->uniqueStrict()->all()
            );
        } finally {
            $lock->release();
        }

    }
}
