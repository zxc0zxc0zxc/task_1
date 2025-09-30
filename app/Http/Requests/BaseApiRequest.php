<?php

namespace App\Http\Requests;

use App\Enums\MethodEnum;
use App\Exceptions\MethodNotFoundException;
use App\Exceptions\RestMethodIsNotAllowedForApiMethodException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class BaseApiRequest extends FormRequest
{
    public const METHOD = 'method';
    private array $addOnRules = [];

    public function rules(): array
    {
        return array_merge([
            self::METHOD => ['required', new Enum(MethodEnum::class)],
        ], $this->addOnRules);
    }

    public function prepareForValidation(): void
    {
        $httpMethod = $this->method();
        $apiMethod = $this->input(self::METHOD);

        $methodEnum = MethodEnum::tryFrom($apiMethod);

        if (!$methodEnum) {
            throw new MethodNotFoundException();
        }

        if (!$methodEnum->supportsHttpMethod($httpMethod)) {

            throw new RestMethodIsNotAllowedForApiMethodException($methodEnum, $methodEnum?->getAvailableMethods() ?? []);

        }

        $this->addOnRules = MethodEnum::from($apiMethod)->getRules();
    }
}
