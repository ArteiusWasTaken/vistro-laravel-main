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
            
            // Encabezado del ticket (alineación centrada)
            $commands .= chr(27)."a".chr(1); // Centrar texto
            $commands .= "NOMBRE DE LA EMPRESA\n";
            $commands .= "DIRECCION DE LA EMPRESA\n";
            $commands .= "TELEFONO: 123-456-7890\n";
            $commands .= "--------------------------------\n";
            
            // Detalles del ticket (alineación izquierda)
            $commands .= chr(27)."a".chr(0); // Alinear izquierda
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
            $commands .= "TOTAL:           $45.00\n";
            $commands .= "--------------------------------\n";
            
            // CÓDIGO DE BARRAS (Versión funcional para TM-T88V)
            $barcodeData = "123456789012"; // 12 dígitos para EAN-13
            
            // Configuración del código de barras
            $commands .= chr(29)."h".chr(100); // Altura (dots) - valor entre 1-255
            $commands .= chr(29)."w".chr(3);   // Ancho (1-6) - 3 es un buen valor medio
            $commands .= chr(29)."H".chr(2);   // Posición del texto: 2 (debajo del barcode)
            $commands .= chr(29)."k".chr(4).$barcodeData.chr(0); // EAN-13 (código 4)
            
            // Alternativa para CODE128 (si prefieres este formato)
            // $commands .= chr(29)."k".chr(73).chr(12).$barcodeData;
            
            // Salto de línea después del código de barras
            $commands .= "\n\n";
            
            // Mensaje final (centrado)
            $commands .= chr(27)."a".chr(1); // Centrar texto
            $commands .= "¡Gracias por su compra!\n";
            $commands .= chr(27)."a".chr(0); // Volver a alineación izquierda
            $commands .= "--------------------------------\n";

            $commands .= "\n\n\n\n\n\n";
            
            // CORTE DE PAPEL (Ajustado para Epson TM-T88V)
            $commands .= chr(29)."V".chr(65); // Corte parcial (GS V 65)
            $commands .= chr(0); // Cantidad de líneas a avanzar (0)
            
            // Alternativa para corte completo (si el parcial no funciona)
            // $commands .= chr(29)."V".chr(66).chr(0);
            
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
