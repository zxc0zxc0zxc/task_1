<?php

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Middleware\ApiTokenAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'checkHealth'])
    ->name('health');


Route::as('api.')
    ->middleware(['api.token'])
    ->group(function () {

        Route::prefix('v1')->group(function () {

            Route::any('/', [ApiController::class, 'handle'])
                ->name('v1');

        });

    });
