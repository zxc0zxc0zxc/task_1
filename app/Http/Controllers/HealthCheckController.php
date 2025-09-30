<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AbstractApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthCheckController extends AbstractApiController
{
    // В отдельный сервис выносить не буду, это для докера
    public function checkHealth(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbHealthy = true;
        } catch (Throwable) {
            $dbHealthy = false;
        }

        try {

            Cache::put('healthcheck', true);
            $value = Cache::get('healthcheck');
            $cacheHealthy = $value !== null;
            Cache::forget('healthcheck');

        } catch (Throwable) {
            $cacheHealthy = false;
        }

        $data = [
            'db' => $dbHealthy,
            'cache' => $cacheHealthy
        ];

        return ($dbHealthy && $cacheHealthy) ?
            $this->successResponse($data) :
            $this->errorResponse("The app is unhealthy. Cache: {$cacheHealthy}; DB: {$dbHealthy}");
    }
}
