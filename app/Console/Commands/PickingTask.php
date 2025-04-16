<?php

    namespace App\Console\Commands;

    use Exception;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Http;

    class PickingTask extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'picking:run';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Llama a la función picking del API';

        /**
         * Execute the console command.
         * @noinspection HttpUrlsUsage
         */
        public function handle(): int
        {
            $url = 'http://psafa-test.ddns.net:2221/api/dev/picking';

            try {
                $response = Http::get($url);

                $this->info('Respuesta: ' . $response->body());
            } catch (Exception $e) {
                $this->error('Error al ejecutar picking: ' . $e->getMessage());
            }

            return 0;
        }
    }
