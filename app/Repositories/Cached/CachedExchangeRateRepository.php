<?php
declare(strict_types=1);

namespace App\Repositories\Cached;

use App\Interfaces\Repositories\ExchangeRateRepositoryInterface;
use App\ValueObjects\AmountValueObject;
use Illuminate\Support\Facades\Cache;

final readonly class CachedExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    private const CACHE_PREFIX = 'exchange-rates:';
    private const TTL = 60;
    private const CACHE_TAG = 'exchange-rates';

    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository
    )
    {
    }

    /** @inheritDoc */
    public function getExchangeRatesForCurrency(int $currencyId): array
    {
        return Cache::tags([self::CACHE_TAG])
            ->remember(self::CACHE_PREFIX . $currencyId, self::TTL, function () use ($currencyId) {
                return $this->exchangeRateRepository->getExchangeRatesForCurrency($currencyId);
            });
    }

    /** @inheritDoc */
    public function findExchangeRateForPair(int $fromCurrencyId, int $toCurrencyId): ?AmountValueObject
    {
        return Cache::tags([self::CACHE_TAG])
            ->remember(self::CACHE_PREFIX . "$fromCurrencyId:$toCurrencyId", self::TTL, function () use ($fromCurrencyId, $toCurrencyId) {
                return $this->exchangeRateRepository->findExchangeRateForPair($fromCurrencyId, $toCurrencyId);
            });
    }

    /** @inheritDoc */
    public function fillRates(array $data): void
    {
        $this->exchangeRateRepository->fillRates($data);
        $this->clearExchangeRatesCache();
    }

    private function clearExchangeRatesCache(): void
    {
        Cache::tags([self::CACHE_TAG])->flush();
    }
}
