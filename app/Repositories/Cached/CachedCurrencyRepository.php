<?php
declare(strict_types=1);

namespace App\Repositories\Cached;

use App\Dto\Currency\CurrencyDto;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\ValueObjects\CurrencySymbolValueObject;
use Illuminate\Support\Facades\Cache;

final readonly class CachedCurrencyRepository implements CurrencyRepositoryInterface
{
    private const CACHE_PREFIX = 'currency:';
    private const CURRENCY_LIST_KEY = 'currencies:list';
    private const CACHE_TTL = 600;
    private const CACHE_TAG = 'currency';


    public function __construct(
        private CurrencyRepositoryInterface $currencyRepository
    )
    {
    }

    /** @inheritDoc */
    public function getCurrency(CurrencySymbolValueObject $symbol): CurrencyDto
    {
        $symbolKey = $symbol->getSymbol();
        $cacheKey = self::CACHE_PREFIX . $symbolKey;

        return Cache::tags([self::CACHE_TAG])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($symbol) {
                return $this->currencyRepository->getCurrency($symbol);
            });
    }

    /** @inheritDoc */
    public function existsBySymbol(string $symbol): bool
    {
        $cacheKey = self::CACHE_PREFIX . $symbol;

        if (Cache::has($cacheKey)) {
            return true;
        }

        if ($this->currencyRepository->existsBySymbol($symbol)) {
            $this->getCurrency(new CurrencySymbolValueObject($symbol));
            return true;
        }

        return false;
    }

    /** @inheritDoc */
    public function getAllCurrencies(): array
    {
        return Cache::tags([self::CACHE_TAG])
            ->remember(self::CURRENCY_LIST_KEY, 600, function () {
                return $this->currencyRepository->getAllCurrencies();
            });
    }

    /** @inheritDoc */
    public function fill(array $data): void
    {
        $this->currencyRepository->fill($data);
        $this->clearCurrenciesCache();
    }

    private function clearCurrenciesCache(): void
    {
        Cache::tags([self::CACHE_TAG])->flush();
    }
}
