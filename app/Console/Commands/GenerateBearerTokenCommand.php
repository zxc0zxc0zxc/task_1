<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateBearerTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bearer-token:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание юзера и возврат его Bearer токена для авторизации';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::factory()->create();

        $bearerToken = $user->createToken('test-token')->plainTextToken;

        $this->info('Bearer token: ' . $bearerToken);

        return self::SUCCESS;
    }
}
