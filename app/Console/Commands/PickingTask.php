<?php

    namespace App\Console\Commands;

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
         */
        public function handle(): int
        {
            $url = 'http://localhost:8001/api/dev/picking';

            try {
                $response = Http::get($url);

                $this->info('Respuesta: ' . $response->body());
            } catch (\Exception $e) {
                $this->error('Error al ejecutar picking: ' . $e->getMessage());
            }

            return 0;
        }
    }
