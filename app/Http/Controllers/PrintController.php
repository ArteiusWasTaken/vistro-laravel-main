<?php

namespace App\Http\Controllers;

use App\Services\ErrorLoggerService;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use stdClass;

/**
 *
 */
class PrintController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function etiquetas(Request $request): JsonResponse
    {
        $output = '';
        $tipo = $request->input('tipo');
        $data = json_decode($request->input('data'));

        $impresora = DB::table('impresora')->where('id', $data->impresora)->first();
        if (!$impresora) {
            return response()->json([
                'code' => 500,
                'message' => 'No se encontró la impresora proporcionada ' . self::logLocation()
            ]);
        }

        $ip = $impresora->ip;
        $tamanio = $impresora->tamanio;
        $port = 9100;

        $etiquetas = ($tipo == '1' && !empty($data->etiquetas)) ? $data->etiquetas : [$data];

        foreach ($etiquetas as $etiqueta) {
            try {
                $command = 'python python/label/' . $tamanio . '/sku_description.py ' .
                    escapeshellarg($etiqueta->codigo) . ' ' .
                    escapeshellarg($etiqueta->descripcion) . ' ' .
                    escapeshellarg($etiqueta->cantidad) . ' ' .
                    escapeshellarg($etiqueta->extra ?? '') . ' 2>&1';

                $output = trim(shell_exec($command));

                $socket = fsockopen($ip, $port, $errno, $errstr, 5);
                if (!$socket) {
                    throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
                }

                fwrite($socket, $output);
                fclose($socket);

            } catch (Exception $e) {
                ErrorLoggerService::log(
                    'Error en etiquetas. Impresora: ' . $ip,
                    'PrintController',
                    [
                        'exception' => $e->getMessage(),
                        'line' => self::logLocation()
                    ]
                );
                return response()->json([
                    'Error' => 'No se pudo imprimir: ' . $e->getMessage()
                ], 500);
            }
        }
        return response()->json([$output]);
    }

    /**
     * @return string
     */
    private static function logLocation(): string
    {
        $sis = 'BE'; // Front o Back
        $ini = 'PC'; // Primera letra del Controlador y Letra de la seguna Palabra: Controller, service
        $fin = 'INT'; // Últimas 3 letras del primer nombre del archivo *comPRAcontroller
        $trace = debug_backtrace()[0];
        return ('<br>' . $sis . $ini . $trace['line'] . $fin);
    }

    /**
     * @return JsonResponse
     * @noinspection PhpUnused
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

            $commands = '';

            // Inicialización básica para GHIA
            $commands .= chr(27) . '@'; // Reset printer
            $commands .= chr(27) . 'R' . chr(0); // Set internacional character set USA
            $commands .= chr(27) . 't' . chr(0); // Codificación UTF-8

            // Encabezado del ticket
            $commands .= chr(27) . 'a' . chr(1); // Centrar texto
            $commands .= "MI EMPRESA\n";
            $commands .= "DIRECCION\n";
            $commands .= "TEL: 123-456-7890\n";
            $commands .= "-----------------------\n";

            // Detalles del ticket
            $commands .= chr(27) . 'a' . chr(0); // Alinear izquierda
            $commands .= 'Fecha: ' . date('d/m/Y H:i:s') . "\n";
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
            $barcodeData = 'ABC123456789'; // Datos alfanuméricos

            // Configuración específica para GHIA:
            $commands .= chr(29) . 'h' . chr(100); // Altura (1-255 dots)
            $commands .= chr(29) . 'w' . chr(2);   // Ancho (1-6, 2 es estándar)
            $commands .= chr(29) . 'f' . chr(0);   // Fuente del texto (0=A, 1=B)
            $commands .= chr(29) . 'H' . chr(2);   // Posición del texto (2=debajo)

            // Comando CODE128 modificado para GHIA:
            $len = strlen($barcodeData);
            $commands .= chr(29) . 'k' . chr(73) . chr($len) . $barcodeData;

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
            $commands .= chr(27) . 'a' . chr(1); // Centrar
            $commands .= "Gracias por su compra\n";
            $commands .= chr(27) . 'a' . chr(0); // Alinear izquierda
            $commands .= "-----------------------\n";

            // Corte de papel para GHIA
            $commands .= chr(29) . 'V' . chr(65) . chr(0); // Corte parcial
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
     * @param $barcode
     * @return JsonResponse
     * @noinspection PhpUnused
     */
    public function tickets_usb($barcode): JsonResponse
    {
        try {
            // 1. Configurar conector - elige una opción:

            // a) Para impresora USB directa (Linux)
            $connector = new FilePrintConnector('/dev/usb/lp0');

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
            // $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Fecha: ' . date('d/m/Y H:i:s') . "\n");
            $printer->text("Ticket #: 12345\n");
            $printer->text("----------------\n");

            // 6. Configurar código de barras (sin constantes)

            // Calcular ancho dinámico según la longitud del código
            $length = strlen($barcode);

            // Establecer un ancho base (entre 1 y 6, recomendado por la mayoría de impresoras)
            $width = match (true) {
                $length >= 16 => 1,
                $length >= 10 => 2,
                $length >= 7 => 3,
                $length >= 5 => 4,
                $length == 4 => 5,
                default => 6,
            };

            // Configurar CODE39 (69 es el tipo numérico para CODE39)
            $printer->setBarcodeHeight(65);
            $printer->setBarcodeWidth($width);
            $printer->setBarcodeTextPosition(2);

            $printer->barcode($barcode, 69);

            // 7. Pie del ticket
            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Gracias por su compra\n");

            // 8. Cortar papel (formato Epson)
            $printer->cut(Printer::CUT_PARTIAL);

            // 9. Cerrar conexión
            $printer->close();

            return response()->json(['success' => true, 'message' => 'Ticket impreso', 'tamanio' => $width]);

        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => 'No se pudo imprimir: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * @return JsonResponse
     * @noinspection PhpUnused
     */
    public function etiquetasData(): JsonResponse
    {
        $impresoras = DB::table('impresora')
            ->where('status', 1)
            ->get()
            ->toArray();

        $empresas = DB::table('empresa')
            ->select('empresa', 'bd')
            ->where('id', '<>', '')
            ->get()
            ->toArray();

        return response()->json([
            'code' => 200,
            'impresoras' => $impresoras,
            'empresas' => $empresas
        ]);
    }

    /**
     * @return ResponseFactory|Response|Application
     */
    public function keepAlive(): ResponseFactory|Response|Application
    {
        $ips = DB::table('impresora')
            ->where('tipo', 1)
            ->pluck('ip')
            ->toArray();

        $port = 9100;

        foreach ($ips as $ip) {
            try {
                $output = '^XA^XZ';
                $socket = fsockopen($ip, $port, $errno, $errstr, 5);

                if (!$socket) {
                    throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
                }

                fwrite($socket, trim(mb_convert_encoding($output, 'UTF-8', 'auto')));
                fclose($socket);

            } catch (Exception $e) {
                ErrorLoggerService::log(
                    'Error en Keep Alive. Impresora: ' . $ip,
                    'PrintController',
                    [
                        'exception' => $e->getMessage(),
                        'line' => self::logLocation()
                    ]
                );
            }
        }
        return response('Keep Alive enviado Correctamente');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function etiquetasSerie(Request $request): JsonResponse
    {
        $output = '';
        $data = json_decode($request->input('data'));
        $etiquetas = [];
        $cantidad = (int)explode('.', $data->cantidad)[0];

        $impresora = DB::table('impresora')->where('id', $data->impresora)->first();
        if (!$impresora) {
            return response()->json([
                'code' => 500,
                'message' => 'No se encontró la impresora proporcionada ' . self::logLocation()
            ]);
        }

        $modelo = DB::table('modelo')
            ->select('id', 'consecutivo', 'descripcion')
            ->where('sku', $data->codigo)
            ->first();

        if (!$modelo) {
            $modelo = DB::table('modelo_sinonimo')
                ->join('modelo', 'modelo_sinonimo.id_modelo', '=', 'modelo.id')
                ->select('modelo.id', 'modelo.consecutivo', 'modelo.descripcion')
                ->where('modelo_sinonimo.codigo', trim($data->codigo))
                ->first();

            if (!$modelo) {
                return response()->json([
                    'code' => 500,
                    'message' => 'El código proporcionado no existe en la base de datos, contactar a un administrador '
                        . self::logLocation()
                ]);
            }
        }

        $fecha = date('mY');
        $prefijo = str_pad(substr($modelo->id, -5), 5, '0', STR_PAD_LEFT);
        $consecutivo_base = (int)$modelo->consecutivo;

        for ($i = 0; $i < $cantidad; $i++) {
            $consecutivo = $consecutivo_base + $i + 1;
            $sufijo = str_pad($consecutivo, 6, '0', STR_PAD_LEFT);

            $etiquetas[] = (object)[
                'serie' => $prefijo . $fecha . $sufijo,
                'codigo' => $data->codigo,
                'descripcion' => $modelo->descripcion,
                'cantidad' => 1,
                'extra' => property_exists($data, 'extra') ? $data->extra : ''
            ];
        }

        $nuevo_consecutivo = ($consecutivo_base + $cantidad >= 800000) ? 1 : ($consecutivo_base + $cantidad);
        DB::table('modelo')->where('id', $modelo->id)->update(['consecutivo' => $nuevo_consecutivo]);

        foreach ($etiquetas as $etiqueta) {
            try {
                $command = 'python python/label/' . $impresora->tamanio . '/sku_description_serie.py ' .
                    escapeshellarg($etiqueta->codigo) . ' ' .
                    escapeshellarg($etiqueta->descripcion) . ' ' .
                    escapeshellarg($etiqueta->serie) . ' ' .
                    escapeshellarg($etiqueta->cantidad) . ' ' .
                    escapeshellarg($etiqueta->extra) . ' 2>&1';

                $output = trim(shell_exec($command));

                $socket = fsockopen($impresora->ip, 9100, $errno, $errstr, 5);
                if (!$socket) {
                    throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
                }

                fwrite($socket, $output);
                fclose($socket);

            } catch (Exception $e) {
                ErrorLoggerService::log(
                    'Error en etiquetas. Impresora: ' . $impresora->ip,
                    'PrintController',
                    ['exception' => $e->getMessage(), 'line' => self::logLocation()]
                );
                return response()->json([
                    'Error' => 'No se pudo imprimir: ' . $e->getMessage()
                ], 500);
            }
        }
        return response()->json([$output]);
    }

    public function rawinfo_picking(): JsonResponse
    {
        $servidores = DB::table('documento')
            ->join('marketplace_area', 'documento.id_marketplace_area', '=', 'marketplace_area.id')
            ->join('empresa_almacen', 'documento.id_almacen_principal_empresa', '=', 'empresa_almacen.id')
            ->join('impresora', 'empresa_almacen.id_impresora_picking', '=', 'impresora.id')
            ->where('documento.id_fase', 3)
            ->where('documento.status', 1)
            ->where('documento.id_tipo', 2)
            ->where('documento.autorizado', 1)
            ->where('documento.problema', 0)
            ->where('documento.picking', 0)
            ->whereYear('documento.created_at', date('Y'))
            ->groupBy('impresora.servidor')
            ->pluck('impresora.servidor')
            ->toArray();

        if (!empty($servidores)) {
            foreach ($servidores as $servidor) {
                $ventas = DB::table('documento')
                    ->join('marketplace_area', 'documento.id_marketplace_area', '=', 'marketplace_area.id')
                    ->join('empresa_almacen', 'documento.id_almacen_principal_empresa', '=', 'empresa_almacen.id')
                    ->join('impresora', 'empresa_almacen.id_impresora_picking', '=', 'impresora.id')
                    ->where('documento.id_fase', 3)
                    ->where('documento.status', 1)
                    ->where('documento.id_tipo', 2)
                    ->where('documento.autorizado', 1)
                    ->where('documento.problema', 0)
                    ->where('documento.picking', 0)
                    ->where('documento.packing_by', 0)
                    ->whereYear('documento.created_at', date('Y'))
                    ->where('impresora.servidor', $servidor)
                    ->where(function ($query) {
                        $query->where('marketplace_area.publico', '!=', 0)
                            ->orWhere(function ($query) {
                                $query->where('documento.pagado', '!=', 0)
                                    ->orWhere('documento.id_periodo', '!=', 1);
                            });
                    })
                    ->select(
                        'documento.id',
                        'documento.pagado',
                        'documento.id_periodo',
                        'documento.id_marketplace_area',
                        'documento.documento_extra',
                        'marketplace_area.publico',
                        'impresora.ip'
                    )
                    ->groupBy('documento.id', 'documento.pagado', 'documento.id_periodo', 'documento.id_marketplace_area', 'documento.documento_extra', 'marketplace_area.publico', 'impresora.ip')
                    ->orderBy('documento.created_at')
                    ->limit(30)
                    ->get()
                    ->toArray();

                if (!empty($ventas)) {

                    foreach ($ventas as $index => $venta) {
                        $movimientos = DB::table('movimiento')->where('id_documento', $venta->id)->first();

                        if (empty($movimientos)) {
                            DB::table('seguimiento')->insert([
                                'id_documento' => $venta->id,
                                'id_usuario' => 1,
                                'seguimiento' => "PICKING: El pedido ha sido mandado a fase PEDIDO debido a que actualmente no contiene productos.
                                Por favor, añada artículos para proceder con la siguiente fase del proceso."
                            ]);

                            DB::table('documento')->where('id', $venta->id)->update([
                                'id_fase' => 1,
                            ]);

                            unset($ventas[$index]);

                            $tiene_series = DB::table('movimiento_producto')
                                ->join('movimiento', 'movimiento.id', '=', 'movimiento_producto.id_movimiento')
                                ->join('producto', 'producto.id', '=', 'movimiento_producto.id_producto')
                                ->where('movimiento.id_documento', $venta->id)
                                ->select('producto.*')
                                ->get();

                            if (empty($tiene_series)) {
                                self::eliminarSeries($venta->id);
                            }
                        }
                    }

                    if (empty($venta)) continue;

                    try {
                        self::picking($ventas);
                    } catch (Exception $e) {
                        ErrorLoggerService::log(
                            'No fue posible imprimir los picking del servidor: ' . $servidor,
                            'PrintController',
                            [
                                'exception' => $e->getMessage(),
                                'line' => self::logLocation()
                            ]
                        );
                    }
                }
            }
        }
        return response()->json([
            'Respuesta' => 'Imprimir picking Finalizado'
        ]);
    }

    private static function eliminarSeries($documento): void
    {
        $info = DB::table('documento')->where('id', $documento)->first();

        if (!empty($info)) {
            if ($info->id_fase != 3) {
                DB::table('documento')->where('id', $documento)->update(['id_fase' => 3]);
            }
        }

        $movimientos = DB::table('movimiento')->where('id_documento', $documento)->get();

        if (!empty($movimientos)) {
            foreach ($movimientos as $movimiento) {
                $mov_produ = DB::table('movimiento_producto')->where('id_movimiento', $movimiento->id)->get();

                if (!empty($mov_produ)) {
                    foreach ($mov_produ as $mov) {
                        DB::table('producto')->where('id', $mov->id_producto)->update(['status' => 1]);

                        DB::table('movimiento_producto')->where('id', $mov->id)->delete();
                    }
                }
            }
        }
    }

    private static function picking($documentos): void
    {
        foreach ($documentos as $documento) {
            $seguimiento = array();

            $info = DB::table('documento')
                ->join('empresa_almacen', 'documento.id_almacen_principal_empresa', '=', 'empresa_almacen.id')
                ->join('empresa', 'empresa_almacen.id_empresa', '=', 'empresa.id')
                ->join('almacen', 'empresa_almacen.id_almacen', '=', 'almacen.id')
                ->join('marketplace_area', 'documento.id_marketplace_area', '=', 'marketplace_area.id')
                ->join('area', 'marketplace_area.id_area', '=', 'area.id')
                ->join('marketplace', 'marketplace_area.id_marketplace', '=', 'marketplace.id')
                ->where('documento.status', 1)
                ->where('documento.id_fase', 3)
                ->where('documento.picking', 0)
                ->where('documento.id', $documento->id)
                ->select(
                    'area.area',
                    'documento.id',
                    'marketplace.marketplace',
                    'empresa.empresa',
                    'almacen.almacen'
                )
                ->first();
            if (empty($info)) {
                ErrorLoggerService::log(
                    'No se encontro informacion del documento solicitado ' . $documento->id,
                    'PrintController',
                    [
                        'exception' => 'Errors',
                        'line' => self::logLocation()
                    ]
                );
                continue;
            }

            $productos = DB::table('movimiento')
                ->join('modelo', 'movimiento.id_modelo', '=', 'modelo.id')
                ->where('movimiento.id_documento', $documento->id)
                ->select(
                    'modelo.sku',
                    'modelo.descripcion',
                    'movimiento.cantidad'
                )
                ->get()
                ->toArray();

            if (empty($productos)) {
                ErrorLoggerService::log(
                    'El documento solicitado ' . $documento->id . 'no contiene productos.',
                    'PrintController',
                    [
                        'exception' => 'Errors',
                        'line' => self::logLocation()
                    ]
                );

                DB::table('seguimiento')->insert([
                    'id_documento' => $documento->id,
                    'id_usuario' => 1,
                    'seguimiento' => "Pedido mandado a fase pedido por falta de información en los productos."
                ]);

                DB::table('documento')->where(['id' => $documento->id])->update([
                    'id_fase' => 1,
                ]);

                continue;
            }

            $seguimientos = DB::table('seguimiento')
                ->join('usuario', 'seguimiento.id_usuario', '=', 'usuario.id')
                ->where('seguimiento.id_documento', $documento->id)
                ->select('seguimiento.*', 'usuario.nombre')
                ->orderBy('seguimiento.created_at', 'desc')
                ->limit(2)
                ->get()
                ->toArray();

            if (count($seguimientos) > 0) {
                foreach ($seguimientos as $seguimientoo) {
                    $seguimiento_data = new stdClass();
                    $re = '/\b(\w)\S*\s*/m';
                    $subst = '$1';
                    $seguimiento_data->usuario = preg_replace($re, $subst, $seguimientoo->nombre) . " (" . $seguimientoo->created_at . ")";
                    $seguimiento_data->seguimiento = strip_tags($seguimientoo->seguimiento);

                    $seguimiento[] = $seguimiento_data;
                }
            }

            try {
                $connector = new NetworkPrintConnector($documento->ip, 9100);
                $printer = new Printer($connector);

                $date = (new DateTime('now', new DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');

                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->feed(2);
                $printer->barcode($info->id);
                $printer->feed();

                $printer->setJustification();
                $printer->text($info->area . " / " . $info->marketplace . "\n");
                $printer->text($info->empresa . " / " . $info->almacen . "\n\n");

                $printer->text("Productos\n");
                $printer->text(str_repeat("-", 48) . "\n");

                foreach ($productos as $producto) {
                    $printer->text($producto->sku . "\n");
                    $printer->text($producto->descripcion . "\n");

                    $printer->setTextSize(2, 2);
                    $printer->text($producto->cantidad . "\n");
                    $printer->setTextSize(1, 1);

                    $printer->text(str_repeat("-", 48) . "\n");
                }

                $printer->feed(2);
                $printer->text("Último seguimiento\n");
                $printer->text(str_repeat("-", 48) . "\n");

                foreach ($seguimiento as $s) {
                    $printer->text($s->usuario . "\n");
                    $printer->text($s->seguimiento . "\n");
                    $printer->text(str_repeat("-", 48) . "\n");
                }

                $printer->feed(2);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text($date . "\n\n");

                $printer->cut();
                $printer->close();

                DB::table('documento')->where(['id' => $documento->id])->update([
                    'picking' => 1,
                    'picking_by' => 1,
                    'picking_date' => date('Y-m-d H:i:s')
                ]);

            } catch (Exception $e) {
                $errorMsg = $e->getMessage();

                ErrorLoggerService::log(
                    'No fue posible imprimir la etiqueta del documento ' . $documento->id,
                    'PrintController',
                    [
                        'exception' => $errorMsg,
                        'line' => self::logLocation()
                    ]
                );

                if (
                    str_contains($errorMsg, "No route to host") ||
                    str_contains($errorMsg, "Transport endpoint is not connected") ||
                    str_contains($errorMsg, "Bad file descriptor") ||
                    str_contains($errorMsg, "Failed to write")
                ) {
                    DB::table('documento')->where('id', $documento["id"])->update([
                        'picking' => 1
                    ]);

                    DB::table('seguimiento')->insert([
                        'id_documento' => $documento["id"],
                        'id_usuario' => 1,
                        'seguimiento' => "Error al imprimir el picking."
                    ]);
                }

                if (str_contains($errorMsg, "error al conectar con la impresora")) {
                    break;
                }

                continue;
            }

        }
    }


}
