<?php

namespace App\Http\Resources;

use App\Dto\Convert\ConvertResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ConvertResponseDto
 */
class ConvertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'currency_from' => $this->currencyFrom->getSymbol(),
            'currency_to' => $this->currencyTo->getSymbol(),
            'value' => $this->value->getAmount(),
            'converted_value' => $this->convertedValue->getAmount(),
            'rate' => $this->rate->getAmount(),
        ];
    }
}
