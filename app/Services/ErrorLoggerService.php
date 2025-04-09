<?php

namespace App\Services;

class ErrorLoggerService
{
    public static function log(string $message, string $file, array $context = []): void
    {
        $logFile = storage_path('logs/' . $file . '/errors.log');
        $date = now()->format('Y-m-d H:i:s');
        $contextString = json_encode($context);

        file_put_contents(
            $logFile,
            "[$date] ERROR: $message | Context: $contextString\n",
            FILE_APPEND
        );
    }
}
