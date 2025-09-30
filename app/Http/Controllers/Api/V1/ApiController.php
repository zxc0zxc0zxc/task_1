<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\MethodEnum;
use App\Exceptions\PairNotFoundException;
use App\Exceptions\SettingIsNotSetException;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\BaseApiRequest;
use App\Http\Resources\ConvertResource;
use App\UseCases\ConvertUseCase;
use App\UseCases\ExchangeRatesUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ApiController extends AbstractApiController
{
    public function __construct(
        private readonly ConvertUseCase       $convertUseCase,
        private readonly ExchangeRatesUseCase $exchangeRatesUseCase
    )
    {
    }

    public function handle(
        BaseApiRequest $request
    ): JsonResponse
    {
        $method = MethodEnum::from($request->validated('method'));

        return match ($method) {
            MethodEnum::CONVERT => $this->handleConvert($request),
            MethodEnum::RATES => $this->handleRates($request)
        };
    }

    /**
     * Конвертация, параметр method = convert
     * @param BaseApiRequest $request
     * @return JsonResponse
     */
    private function handleConvert(BaseApiRequest $request): JsonResponse
    {
        $validatedData = collect($request->validated());

        try {

            $result = $this->convertUseCase->execute($validatedData);

            return $this->successResponse(
                ConvertResource::make($result)
            );

        } catch (PairNotFoundException $e) {

            return $this->errorResponse($e->getMessage());

        } catch (SettingIsNotSetException $e) {

            Log::channel('controllers')
                ->critical($e->getMessage());
            return $this->errorResponse();

        } catch (Throwable $e) {

            Log::channel('controllers')
                ->error('An exception caught while executing convert use case', [
                    'from' => __CLASS__,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

            return $this->errorResponse();

        }
    }

    /**
     * Получение курсов, параметр method = rates
     * @param BaseApiRequest $request
     * @return JsonResponse
     */
    private function handleRates(BaseApiRequest $request): JsonResponse
    {
        /** @var string $currency */
        $currency = collect($request->validated())->get('currency');

        try {

            $result = $this->exchangeRatesUseCase->execute($currency);

            return $this->successResponse($result);

        } catch (SettingIsNotSetException $e) {

            Log::channel('controllers')
                ->critical($e->getMessage());
            return $this->errorResponse();

        } catch (Throwable $e) {

            Log::channel('controllers')
                ->error('An exception caught while executing exchange rates use case', [
                    'from' => __CLASS__,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

            return $this->errorResponse();

        }
    }
}
