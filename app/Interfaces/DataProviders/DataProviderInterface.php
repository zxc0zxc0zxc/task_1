<?php
declare(strict_types=1);

namespace App\Interfaces\DataProviders;

use App\Dto\ExchangeRate\ExchangeRateResponseDto;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

interface DataProviderInterface
{

    /**
     * Получение списка обменных курсов
     * @return array<ExchangeRateResponseDto>
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getRates(): array;
}
