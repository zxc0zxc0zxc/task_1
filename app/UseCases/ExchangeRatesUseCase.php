<?php
declare(strict_types=1);

namespace App\UseCases;

use App\Dto\ExchangeRate\ExchangeRateDto;
use App\Exceptions\SettingIsNotSetException;
use App\Interfaces\Services\ExchangeRateServiceInterface;
use App\ValueObjects\CurrencySymbolValueObject;

final readonly class ExchangeRatesUseCase
{
    public function __construct(
        private ExchangeRateServiceInterface $ratesService
    )
    {

    }

    /**
     * @return array<ExchangeRateDto>
     * @throws SettingIsNotSetException
     */
    public function execute(string $symbol): array
    {
        $symbolVO = new CurrencySymbolValueObject($symbol);

        return $this->ratesService->handle($symbolVO);
    }
}
