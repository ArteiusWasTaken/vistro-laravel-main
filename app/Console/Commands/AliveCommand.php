<?php

namespace App\Console\Commands;

use App\Http\Controllers\PrintController;
use App\Services\ErrorLoggerService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AliveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:alive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imprime alive cada minuto';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            PrintController::class->keepAlive();
            \Log::info('alive command was triggered');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

}
