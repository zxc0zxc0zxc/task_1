<?php

namespace App\UseCases;

use App\Dto\Convert\ConvertDto;
use App\Dto\Convert\ConvertResponseDto;
use App\Exceptions\PairNotFoundException;
use App\Exceptions\SettingIsNotSetException;
use App\Interfaces\Services\ConvertServiceInterface;
use App\ValueObjects\AmountValueObject;
use App\ValueObjects\CurrencySymbolValueObject;
use Illuminate\Support\Collection;

final readonly class ConvertUseCase
{
    public function __construct(
        private ConvertServiceInterface $convertService
    )
    {
    }

    /**
     * @throws PairNotFoundException
     * @throws SettingIsNotSetException
     */
    public function execute(Collection $validatedData): ConvertResponseDto
    {
        $convertDto = new ConvertDto(
            currencyFrom: new CurrencySymbolValueObject($validatedData->get('currency_from')),
            currencyTo: new CurrencySymbolValueObject($validatedData->get('currency_to')),
            value: new AmountValueObject($validatedData->get('value'))
        );

        return $this->convertService->handle($convertDto);
    }
}
