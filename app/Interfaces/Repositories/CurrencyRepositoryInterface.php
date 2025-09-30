<?php
declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Dto\Currency\CurrencyDto;
use App\ValueObjects\CurrencySymbolValueObject;

interface CurrencyRepositoryInterface
{

    /**
     * Получение валюты как дто
     * @param CurrencySymbolValueObject $symbol
     * @return CurrencyDto
     */
    public function getCurrency(CurrencySymbolValueObject $symbol): CurrencyDto;

    /**
     * Проверка на существование валюты
     * @param string $symbol
     * @return bool
     */
    public function existsBySymbol(string $symbol): bool;

    /**
     * Получение всех монет
     * @return array<CurrencyDto>
     */
    public function getAllCurrencies(): array;

    /**
     * Заливка недостающих монет
     * @param array<CurrencySymbolValueObject> $data
     * @return void
     */
    public function fill(array $data): void;
}
