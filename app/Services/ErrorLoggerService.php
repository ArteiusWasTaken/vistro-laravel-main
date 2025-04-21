<?php

namespace App\Services;

/**
 *
 */
class ErrorLoggerService
{
    /**
     * @param string $message
     * @param string $file
     * @param array $context
     * @return void
     */
    public static function logger(string $message, string $file, array $context = []): void
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
