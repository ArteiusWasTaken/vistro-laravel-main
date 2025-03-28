<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

/**
 *
 */
class KeepPrinterAlive extends Command
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
    protected $description = 'Send keep-alive command to the Zebra printer';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        try {
            $command = 'python python/label/2x1/sku_description.py ' .
                escapeshellarg('FDMVVS69495') . ' ' .
                escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
                escapeshellarg('1') . ' ' .
                escapeshellarg('Cod. Universal: 8011003809219') . ' 2>&1';

            $output = shell_exec($command);
            //            $command = "python python/label/2x1/sku_description_serie.py " .
            //                escapeshellarg("FDMVVS69495") . " " .
            //                escapeshellarg("Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre") . " " .
            //                escapeshellarg("FDMVVS69495") . " " .
            //                escapeshellarg("1") . " " .
            //                escapeshellarg("") . " 2>&1";
            //
            //            $output = shell_exec($command);
            //            $command = "python python/label/2x1/description.py " .
            //                escapeshellarg("Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre") . " " .
            //                escapeshellarg("1") . " 2>&1";
            //
            //            $output = shell_exec($command);

            $connector = new NetworkPrintConnector('192.168.100.7', 9100);
            $printer = new Printer($connector);

            $printer->text(trim(mb_convert_encoding($output, 'UTF-8', 'auto')));
            $printer->close();
            $this->info('Keep-alive command sent successfully.');

        } catch (Exception $exception) {
            $this->error('Error sending keep-alive command: ' . $exception->getMessage());

        }
    }
}
