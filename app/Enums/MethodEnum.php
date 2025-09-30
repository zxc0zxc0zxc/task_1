<?php
declare(strict_types=1);

namespace App\Enums;

use App\Interfaces\Repositories\CurrencyRepositoryInterface;
use App\Rules\AmountRule;
use App\Rules\CurrencyExistsRule;
use App\Rules\CurrencySymbolRule;
use Illuminate\Validation\Rules\Enum;

enum MethodEnum: string
{
    case RATES = 'rates';

    case CONVERT = 'convert';

    /**
     * Отдельные правила валидации для каждого из методов
     * @return array[]
     */
    public function getRules(): array
    {
        /** @var CurrencyRepositoryInterface $currencyRepository */
        $currencyRepository = resolve(CurrencyRepositoryInterface::class);

        return match ($this) {
            self::CONVERT => [
                'method' => ['required', new Enum(__CLASS__)],
                'currency_from' => ['required', new CurrencySymbolRule(), new CurrencyExistsRule($currencyRepository)],
                'currency_to' => ['required', new CurrencySymbolRule(), new CurrencyExistsRule($currencyRepository)],
                'value' => ['required', new AmountRule()],
            ],
            self::RATES => [
                'method' => ['required', new Enum(__CLASS__)],
                'currency' => ['required', new CurrencySymbolRule(), new CurrencyExistsRule($currencyRepository)],
            ],
        };
    }

    /**
     * Проверка на поддержку типа запроса
     * @param string $httpMethod
     * @return bool
     */
    public function supportsHttpMethod(string $httpMethod): bool
    {
        return in_array($httpMethod, $this->getAvailableMethods(), true);
    }

    public function getAvailableMethods(): array
    {
        return match ($this) {
            self::CONVERT => ['POST'],
            self::RATES => ['GET'],
        };
    }
}
