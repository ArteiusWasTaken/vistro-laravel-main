<?php
namespace App\Services;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
class KeepAliveService
{
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

    private static function logLocation(): string
    {
        $sis = 'BE'; // Front o Back
        $ini = 'KS'; // Primera letra del Controlador y Letra de la seguna Palabra: Controller, service
        $fin = 'IVE'; // Últimas 3 letras del primer nombre del archivo *comPRAcontroller
        $trace = debug_backtrace()[0];
        return ('<br>' . $sis . $ini . $trace['line'] . $fin);
    }

}
