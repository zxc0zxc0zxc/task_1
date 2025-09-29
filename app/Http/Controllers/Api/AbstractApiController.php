<?php

namespace App\Http\Controllers\Api;

use App\Enums\ResponseStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends Controller
{
    /**
     * @param array|AnonymousResourceCollection|JsonResource $data
     * @return JsonResponse
     */
    public function successResponse(
        array|AnonymousResourceCollection|JsonResource $data
    ): JsonResponse
    {
        return response()
            ->json([
                'status' => ResponseStatusEnum::SUCCESS->value,
                'code' => Response::HTTP_OK,
                'data' => $data instanceof JsonResource
                    ? $data->resolve(request())
                    : $data,
            ])
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function errorResponse(
        string $message = 'Server error.',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse
    {
        return response()
            ->json([
                'status' => ResponseStatusEnum::ERROR->value,
                'code' => $statusCode,
                'message' => $message,
            ])
            ->setStatusCode($statusCode);
    }
}
