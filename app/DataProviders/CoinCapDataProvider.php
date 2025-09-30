<?php
declare(strict_types=1);

namespace App\DataProviders;

use App\Dto\ExchangeRate\ExchangeRateResponseDto;
use App\Interfaces\DataProviders\DataProviderInterface;
use App\ValueObjects\AmountValueObject;
use App\ValueObjects\CurrencySymbolValueObject;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final readonly class CoinCapDataProvider implements DataProviderInterface
{
    private const ENDPOINT = 'https://api.coingate.com/api/v2/rates';

    /** @inheritDoc */
    public function getRates(): array
    {
        $data = $this->request();

        $result = [];

        foreach ($data as $fromCurrency => $toCurrencies) {
            foreach ($toCurrencies as $toCurrency => $rate) {

                if (bccomp('0', $rate, 16) === 0 || bccomp('0', $rate, 16) === 1) {
                    continue;
                }

                // основная пара
                $result[] = new ExchangeRateResponseDto(
                    fromCurrency: new CurrencySymbolValueObject($fromCurrency),
                    toCurrency: new CurrencySymbolValueObject($toCurrency),
                    rate: new AmountValueObject($rate)
                );

                // обратная
                $result[] = new ExchangeRateResponseDto(
                    fromCurrency: new CurrencySymbolValueObject($toCurrency),
                    toCurrency: new CurrencySymbolValueObject($fromCurrency),
                    rate: new AmountValueObject(bcdiv('1', $rate, 16))
                );
            }
        }

        return $result;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    private function request(): array
    {
        return Http::withoutVerifying()
            ->timeout(3)
            ->retry(3)
            ->get(self::ENDPOINT)
            ->throw()
            ->json('merchant');
    }
}
