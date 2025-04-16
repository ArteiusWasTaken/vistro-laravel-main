<?php

use App\Http\Controllers\PrintController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'dev'], function () {
    Route::get('usb/{barcode}', [PrintController::class, 'tickets_usb']);
});

Route::group(['middleware' => [JwtMiddleware::class]], function () {
    Route::group(['prefix' => 'etiquetas'], function () {
        Route::get('/data', [PrintController::class, 'etiquetasData']);

        Route::post('/', [PrintController::class, 'etiquetas']);
        Route::post('/serie', [PrintController::class, 'etiquetasSerie']);
    });

    Route::group(['prefix' => 'tickets'], function () {
        Route::get('/', [PrintController::class, 'tickets']);
    });
});
