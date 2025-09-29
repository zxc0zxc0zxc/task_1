<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'checkHealth'])
    ->name('health');
