<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

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

            //$output = '^XA ZPL & PDF ^XZ';
            $command = 'python3 python/afa/pdf_to_zpl.py ' . escapeshellarg('img/test/label.pdf') . ' 2>&1';
            $output = trim(trim(shell_exec($command)));

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
            
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            if (!$socket) {
                throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
            }
            
            $commands = "";
            
            // Inicialización básica para GHIA
            $commands .= chr(27)."@"; // Reset printer
            $commands .= chr(27)."R".chr(0); // Set internacional character set USA
            $commands .= chr(27)."t".chr(0); // Codificación UTF-8
            
            // Encabezado del ticket
            $commands .= chr(27)."a".chr(1); // Centrar texto
            $commands .= "MI EMPRESA\n";
            $commands .= "DIRECCION\n";
            $commands .= "TEL: 123-456-7890\n";
            $commands .= "-----------------------\n";
            
            // Detalles del ticket
            $commands .= chr(27)."a".chr(0); // Alinear izquierda
            $commands .= "Fecha: ".date('d/m/Y H:i:s')."\n";
            $commands .= "Ticket #: 12345\n";
            $commands .= "-----------------------\n";
            $commands .= "PRODUCTO       CANT  TOTAL\n";
            $commands .= "-----------------------\n";
            $commands .= "Producto 1     1    $10.00\n";
            $commands .= "Producto 2     2    $20.00\n";
            $commands .= "-----------------------\n";
            $commands .= "TOTAL:        $30.00\n";
            $commands .= "-----------------------\n\n";
            
            // CÓDIGO DE BARRAS CODE128 PARA GHIA GTP-801
            $barcodeData = "ABC123456789"; // Datos alfanuméricos
            
            // Configuración específica para GHIA:
            $commands .= chr(29)."h".chr(100); // Altura (1-255 dots)
            $commands .= chr(29)."w".chr(2);   // Ancho (1-6, 2 es estándar)
            $commands .= chr(29)."f".chr(0);   // Fuente del texto (0=A, 1=B)
            $commands .= chr(29)."H".chr(2);   // Posición del texto (2=debajo)
            
            // Comando CODE128 modificado para GHIA:
            $len = strlen($barcodeData);
            $commands .= chr(29)."k".chr(73).chr($len).$barcodeData;
            
            /* 
            Estructura especial para GHIA GTP-801:
            1D 6B 49 [n] [data] 
            Donde:
            - 1D 6B: Inicio código de barras
            - 49: Selecciona CODE128 (73 en decimal)
            - [n]: Longitud de los datos (1 byte)
            - [data]: Los datos del código
            */
            
            $commands .= "\n\n\n\n"; // Espacios después del código
            
            // Pie del ticket
            $commands .= chr(27)."a".chr(1); // Centrar
            $commands .= "Gracias por su compra\n";
            $commands .= chr(27)."a".chr(0); // Alinear izquierda
            $commands .= "-----------------------\n";
            
            // Corte de papel para GHIA
            $commands .= chr(29)."V".chr(65).chr(0); // Corte parcial
            // Alternativa: $commands .= chr(29)."V".chr(66).chr(0); // Corte completo
            
            // Enviar comandos
            fwrite($socket, $commands);
            fclose($socket);
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket impreso',
                'hex_sent' => bin2hex($commands) // Para depuración
            ]);
            
            // CÓDIGO DE BARRAS (Versión funcional para TM-T88V)
            /* EAN 13 funciona
            $barcodeData = "123456789012"; // 12 dígitos para EAN-13
            
            // Configuración del código de barras
            $commands .= chr(29)."h".chr(100); // Altura (dots) - valor entre 1-255
            $commands .= chr(29)."w".chr(3);   // Ancho (1-6) - 3 es un buen valor medio
            $commands .= chr(29)."H".chr(2);   // Posición del texto: 2 (debajo del barcode)
            $commands .= chr(29)."k".chr(4).$barcodeData.chr(0); // EAN-13 (código 4)
            */
            
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => 'No se pudo imprimir: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function tickets_usb(): JsonResponse
    {
        try {
            // 1. Configurar conector - elige una opción:
    
            // a) Para impresora USB directa (Linux)
            $connector = new FilePrintConnector("/dev/usb/lp0");
            
            // b) Para impresora de red
            // $connector = new NetworkPrintConnector("192.168.1.100", 9100);
            
            // 2. Crear instancia de impresora
            $printer = new Printer($connector);
            
            // 3. Configuración inicial
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            
            // 4. Encabezado del ticket
            $printer->text("MI EMPRESA\n");
            $printer->text("DIRECCION\n");
            $printer->text("TEL: 123-456-7890\n");
            $printer->text("----------------\n");
            
            // 5. Detalles del ticket
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha: ".date('d/m/Y H:i:s')."\n");
            $printer->text("Ticket #: 12345\n");
            $printer->text("----------------\n");
            
            // 6. Configurar código de barras (sin constantes)
            $barcodeData = "1534134111"; // Tus datos numéricos
            
            // Configuración del código de barras:
            // - 65 = CODE128-A (caracteres estándar)
            // - 66 = CODE128-B (alfanumérico)
            // - 67 = CODE128-C (numérico puro, más compacto)
            
            // Configurar CODE39 (69 es el tipo numérico para CODE39)
            $printer->setBarcodeHeight(80);
            //$printer->setBarcodeWidth(3);
            $printer->setBarcodeTextPosition(2); // Texto debajo
            
            // Imprimir código de barras CODE39
            $printer->barcode($barcodeData, 69); // 69 = CODE39
            
            // 7. Pie del ticket
            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Gracias por su compra\n");
            
            // 8. Cortar papel (formato Epson)
            $printer->cut(Printer::CUT_PARTIAL);
            
            // 9. Cerrar conexión
            $printer->close();
            
            return response()->json(['success' => true, 'message' => 'Ticket impreso']);
            
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
