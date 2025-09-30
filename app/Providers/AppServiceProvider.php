<?php

namespace App\Providers;

use App\Interfaces\Pipelines\DataProviderPipelineInterface;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\Interfaces\Repositories\ExchangeRateRepositoryInterface;
use App\Interfaces\Repositories\SettingRepositoryInterface;
use App\Interfaces\Services\ConvertServiceInterface;
use App\Interfaces\Services\ExchangeRateServiceInterface;
use App\Pipelines\DataProviderPipeline;
use App\Repositories\Cached\CachedCurrencyRepository;
use App\Repositories\Cached\CachedExchangeRateRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\ExchangeRateRepository;
use App\Repositories\SettingRepository;
use App\Services\ConvertService;
use App\Services\ExchangeRateService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories

        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(ExchangeRateRepositoryInterface::class, CachedExchangeRateRepository::class);

        $this->app->when(CachedExchangeRateRepository::class)
            ->needs(ExchangeRateRepositoryInterface::class)
            ->give(ExchangeRateRepository::class);

        $this->app->bind(CurrencyRepositoryInterface::class, CachedCurrencyRepository::class);

        $this->app->when(CachedCurrencyRepository::class)
            ->needs(CurrencyRepositoryInterface::class)
            ->give(CurrencyRepository::class);

        // Services

        $this->app->bind(ConvertServiceInterface::class, ConvertService::class);
        $this->app->bind(ExchangeRateServiceInterface::class, ExchangeRateService::class);

        // Pipelines

        $this->app->bind(DataProviderPipelineInterface::class, DataProviderPipeline::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
