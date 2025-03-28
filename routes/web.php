<?php

use App\Http\Controllers\PrintController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return phpinfo();
});


Route::middleware([JwtMiddleware::class])->group(function () {
    Route::prefix('impresoras')->group(function () {
        Route::get('/', [PrintController::class, 'index']);
    });
});
