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

            $output = '^XA ZPL & PDF ^XZ';
//            $command = 'python3 python/afa/pdf_to_zpl.py ' . escapeshellarg('img/test/label.pdf') . ' 2>&1';
//            $output = trim(trim(shell_exec($command)));

            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }

            fwrite($socket, $output);
            fclose($socket);

        } catch (Exception $exception) {
            return response()->json([
                'Error' => 'No se pudo imprimir: ' . $exception->getMessage()
            ], 500);
        }


//        try {
//            $ip = '192.168.15.72';
//            $port = 9100;
//
////            $output2 = '^XA ZPL & PDF ^XZ';
//            $command = 'python3 python/afa/img_to_zpl.py ' . escapeshellarg('img/test/omg.png') . ' 2>&1';
//            $output2 = trim(shell_exec($command));
//
//            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
//            if (!$socket) {
//                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
//            }
//
//            fwrite($socket, $output2);
//            fclose($socket);
//
//        } catch (Exception $exception) {
//            return response()->json([
//                'Error' => 'No se pudo imprimir: ' . $exception->getMessage()
//            ], 500);
//        }

        return response()->json([
            'Respuesta' => 'Enviado correctamente',
            'data' => $output,
//            'data2' => $output2
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function tickets(): JsonResponse
    {
        try {
            $ip = '192.168.15.73';
            $port = 9100;
            
            // Conectar a la impresora
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }
            
            // Configuración inicial de la impresora
            $commands = "";
            
            // Inicializar impresora (ESC/POS)
            $commands .= chr(27)."@"; // Reset printer
            
            // Establecer alineación centrada
            $commands .= chr(27)."a".chr(1);
            
            // Encabezado del ticket
            $commands .= "NOMBRE DE LA EMPRESA\n";
            $commands .= "DIRECCION DE LA EMPRESA\n";
            $commands .= "TELEFONO: 123-456-7890\n";
            $commands .= "--------------------------------\n";
            
            // Volver a alineación izquierda
            $commands .= chr(27)."a".chr(0);
            
            // Detalles del ticket
            $commands .= "Fecha: ".date('d/m/Y H:i:s')."\n";
            $commands .= "Ticket #: 12345\n";
            $commands .= "Atendió: Juan Perez\n";
            $commands .= "--------------------------------\n";
            $commands .= "PRODUCTO          CANT  PRECIO\n";
            $commands .= "--------------------------------\n";
            $commands .= "Producto 1        1     $10.00\n";
            $commands .= "Producto 2        2     $20.00\n";
            $commands .= "Producto 3        1     $15.00\n";
            $commands .= "--------------------------------\n";
            
            // Total
            $commands .= "TOTAL:           $45.00\n";
            $commands .= "--------------------------------\n";
            
            // Agregar código de barras (CODE 128)
            // Primero configuramos el código de barras
            $barcodeData = "123456789012"; // Datos del código de barras
            
            // Configurar posición del código de barras (centrado)
            $commands .= chr(27)."a".chr(1);
            
            // Imprimir texto debajo del código de barras
            $commands .= chr(29)."h".chr(60); // Altura del código de barras
            $commands .= chr(29)."w".chr(2); // Ancho del código de barras
            $commands .= chr(29)."k".chr(73).chr(12).$barcodeData; // CODE 128
            
            // Texto debajo del código de barras
            $commands .= "\n".$barcodeData."\n";
            
            // Volver a alineación izquierda
            $commands .= chr(27)."a".chr(0);
            
            // Mensaje final
            $commands .= "¡Gracias por su compra!\n";
            $commands .= "--------------------------------\n";
            
            // Cortar papel (completo o parcial)
            $commands .= chr(29)."V".chr(66).chr(0); // Cortar papel (GS V 66)
            
            // Enviar comandos a la impresora
            fwrite($socket, $commands);
            fclose($socket);
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket impreso correctamente',
                'data' => bin2hex($commands) // Para depuración
            ]);
            
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => 'No se pudo imprimir: ' . $exception->getMessage()
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
//$output = trim(shell_exec($command));

//            $command = 'python python/label/2x1/sku_description.py ' .
//                escapeshellarg('FDMVVS69495') . ' ' .
//                escapeshellarg('Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre') . ' ' .
//                escapeshellarg('1') . ' ' .
//                escapeshellarg('Cod. Universal: 8011003809219') . ' 2>&1';
//
//            $output = trim(shell_exec($command));

//            $command = "python python/label/2x1/sku_description_serie.py " .
//                escapeshellarg("FDMVVS69495") . " " .
//                escapeshellarg("Perfume Versace Eros Eau De Toilette 100 Ml Para Hombre") . " " .
//                escapeshellarg("FDMVVS69495") . " " .
//                escapeshellarg("1") . " " .
//                escapeshellarg("") . " 2>&1";
//
//            $output = trim(shell_exec($command));
