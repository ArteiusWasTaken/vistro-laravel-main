<?php

use App\Http\Controllers\PrintController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return phpinfo();
});

Route::prefix('dev')->group(function () {
    Route::get('/ka', [PrintController::class, 'keepAlive']);

    Route::get('usb/{barcode}/{tamanio}', [PrintController::class, 'tickets_usb']);
});

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::prefix('etiquetas')->group(function () {
        Route::get('/', [PrintController::class, 'etiquetas']);
    });
    Route::prefix('tickets')->group(function () {
        Route::get('/', [PrintController::class, 'tickets']);
    });
});
