<?php

use App\Jobs\UpdateCurrenciesTableJob;
use App\Jobs\UpdateExchangeRatesJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(UpdateCurrenciesTableJob::class)
    ->hourly();

Schedule::job(UpdateExchangeRatesJob::class)
    ->everyMinute();
