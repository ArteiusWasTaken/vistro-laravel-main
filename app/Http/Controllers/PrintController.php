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
    public function etiquetas(): JsonResponse
    {
        try {
            $ip = '192.168.15.72';
            $port = 9100;

//            $output = '^XA^CI28^LH0,0^FO25,15^BY2,,0^BCN,55,N,N^FDMVVS69495^FS^FT110,98^A0N,22,22^FH^FDMVVS69495^FS^FT109,98^A0N,22,22^FH^FDMVVS69495^FS^FO22,115^A0N,18,18^FB300,2,0,L^FH^FDPerfume Versace Eros Eau De Toilette 100 Ml Para Hombre^FS^FO22,153^A0N,18,18^FB300,1,0,L^FH^FD^FS^FO21,153^A0N,18,18^FB300,1,0,L^FH^FD^FS^FO22,175^A0N,18,18^FH^FDCod. Universal: 8011003809219^FS^FO22,175^A0N,18,18^FH^FD^FS^PQ1,0,1,Y^XZ';
            $output = '^XA^FX Top section with logo, name and address.^CF0,60^FO100,50^FDEtiqueta^FS^CF0,30^XZ';
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }

            fwrite($socket, $output);
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
     * @return JsonResponse
     */
    public function tickets(): JsonResponse
    {
        try {
            $ip = '192.168.15.73';
            $port = 9100;

//            $output = '^XA^CI28^LH0,0^FO25,15^BY2,,0^BCN,55,N,N^FDMVVS69495^FS^FT110,98^A0N,22,22^FH^FDMVVS69495^FS^FT109,98^A0N,22,22^FH^FDMVVS69495^FS^FO22,115^A0N,18,18^FB300,2,0,L^FH^FDPerfume Versace Eros Eau De Toilette 100 Ml Para Hombre^FS^FO22,153^A0N,18,18^FB300,1,0,L^FH^FD^FS^FO21,153^A0N,18,18^FB300,1,0,L^FH^FD^FS^FO22,175^A0N,18,18^FH^FDCod. Universal: 8011003809219^FS^FO22,175^A0N,18,18^FH^FD^FS^PQ1,0,1,Y^XZ';
            $output = '^XA^FX Top section with logo, name and address.^CF0,60^FO100,50^FDTicket^FS^CF0,30^XZ';
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }

            fwrite($socket, $output);
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
            $ip = '192.168.15.72';
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

//$command = 'python python/label/2x1/description.py ' .
//    escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
//    escapeshellarg('1') . ' 2>&1';
//
//$output = shell_exec($command);

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
