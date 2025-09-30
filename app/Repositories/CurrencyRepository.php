<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Currency\CurrencyDto;
use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\Models\Currency;
use App\ValueObjects\CurrencySymbolValueObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CurrencyRepository implements CurrencyRepositoryInterface
{

    /** @inheritDoc */
    public function getCurrency(CurrencySymbolValueObject $symbol): CurrencyDto
    {
        $data = Currency::query()
            ->select(['currencies.id'])
            ->where('currencies.symbol', '=', $symbol->getSymbol())
            ->first();

        return new CurrencyDto(
            id: $data->id,
            symbolValueObject: $symbol
        );
    }

    /** @inheritDoc */
    public function existsBySymbol(string $symbol): bool
    {
        return Currency::query()
            ->where('currencies.symbol', '=', $symbol)
            ->exists();
    }

    public function getAllCurrencies(): array
    {
        $data = [];

        Currency::query()->chunk(100, function (Collection $currencies) use (&$data) {

            /** @var Currency $currency */
            foreach ($currencies as $currency) {

                $data[] = new CurrencyDto(
                    id: $currency->id,
                    symbolValueObject: new CurrencySymbolValueObject($currency->symbol)
                );

            }

        });

        return $data;
    }

    /** @inheritDoc */
    public function fill(array $data): void
    {
        $symbols = collect($data)
            ->map(fn(CurrencySymbolValueObject $symbol) => $symbol->getSymbol())
            ->uniqueStrict()
            ->values()
            ->all();

        $existingSymbols = Currency::query()
            ->whereIn('symbol', $symbols)
            ->pluck('symbol')
            ->flip()
            ->all();

        $toBeCreated = array_filter($symbols, static fn($symbol) => !isset($existingSymbols[$symbol]));

        $this->insertNewCurrencies($toBeCreated);
    }

    /**
     * Вставка новых монет
     * @param array $data
     * @return void
     */
    private function insertNewCurrencies(array $data): void
    {
        $chunks = array_chunk($data, 500);

        foreach ($chunks as $chunk) {
            $insertData = array_map(static fn($symbol) => [
                'symbol' => $symbol
            ], $chunk);

            DB::transaction(static function () use ($insertData) {
                Currency::query()->insert($insertData);
            }, 3);
        }
    }
}
