<?php

namespace App\Console\Commands;

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
            $connector = new NetworkPrintConnector('192.168.100.7', 9100);
            $printer = new Printer($connector);

            $printer->text('^^XA^FO50,50^FDKEEP ALIVE^FS^XZ');
            $printer->cut();
            $printer->close();

            $this->info('Keep-alive command sent successfully.');
        } catch (\Exception $exception) {
            $this->error('Error sending keep-alive command: ' . $exception->getMessage());
        }
    }
}
