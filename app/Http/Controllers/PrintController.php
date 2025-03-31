<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;

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
            $ip = '172.16.200.63';
            $port = 9100;

            $command = 'python python/label/2x1/sku_description.py ' .
                escapeshellarg('FDMVVS69495') . ' ' .
                escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
                escapeshellarg('1') . ' ' .
                escapeshellarg('Cod. Universal: 8011003809219') . ' 2>&1';

            $output = shell_exec($command);
//
//            $command = 'python python/label/2x1/description.py ' .
//                escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
//                escapeshellarg('1') . ' 2>&1';
//
//            $output = shell_exec($command);
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }

            fwrite($socket, trim(mb_convert_encoding($output, 'UTF-8', 'auto')));
            fclose($socket);

            return response()->json([
                'Respuesta' => 'Enviado correctamente'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'Error' => 'No se pudo imprimir: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * @return void
     */
    public function keepAlive(): void
    {
        try {
            $ip = '172.16.200.63';
            $port = 9100;

            $output = '^XA^XZ';
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);

            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }

            fwrite($socket, trim(mb_convert_encoding($output, 'UTF-8', 'auto')));
            fclose($socket);
        } catch (Exception) {
        }
    }
}

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
