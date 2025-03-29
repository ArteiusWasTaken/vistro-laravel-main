<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

/**
 *
 */
class SendKeepAliveToPrinter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printer:keep-alive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar un comando vacío a la impresora Zebra para evitar que entre en standby';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $ip = '192.168.100.7';
            $port = 9100;

            $output = '^XA^XZ'; // Comando vacío ZPL
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);

            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }

            fwrite($socket, trim(mb_convert_encoding($output, 'UTF-8', 'auto')));
            fclose($socket);

            $this->info('Comando vacío enviado correctamente a la impresora.');
        } catch (Exception $exception) {
            $this->error('Error al enviar el comando: ' . $exception->getMessage());
        }
    }
}
