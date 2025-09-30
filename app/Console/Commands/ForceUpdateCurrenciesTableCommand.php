<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCurrenciesTableJob;
use Illuminate\Console\Command;

class ForceUpdateCurrenciesTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:force-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Принудительное обновление таблицы с монетами';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dispatch(new UpdateCurrenciesTableJob());
    }
}
