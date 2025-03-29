<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

/**
 *
 */
class PrintController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
//            $command = 'python python/label/2x1/sku_description.py ' .
//                escapeshellarg('FDMVVS69495') . ' ' .
//                escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
//                escapeshellarg('1') . ' ' .
//                escapeshellarg('Cod. Universal: 8011003809219') . ' 2>&1';
//
//            $output = shell_exec($command);
//            $command = "python python/label/2x1/sku_description_serie.py " .
//                escapeshellarg("FDMVVS69495") . " " .
//                escapeshellarg("Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre") . " " .
//                escapeshellarg("FDMVVS69495") . " " .
//                escapeshellarg("1") . " " .
//                escapeshellarg("") . " 2>&1";
//
//            $output = shell_exec($command);
            $command = 'python3 python/label/2x1/description.py ' .
                escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
                escapeshellarg('1') . ' 2>&1';

            $output = shell_exec($command);

            $connector = new NetworkPrintConnector('192.168.100.7', 9100);
            $printer = new Printer($connector);

            $printer->text(trim(mb_convert_encoding($output, 'UTF-8', 'auto')));
            $printer->close();
        } catch (Exception $exception) {
            echo 'No se pudo imprimir: ' . $exception->getMessage();
        }
        return response()->json([
            'Respuesta' => trim(mb_convert_encoding($output, 'UTF-8', 'auto'))

        ]);
    }

}
