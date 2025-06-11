<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'auth'], function () {
    Route::get('check', [AuthController::class, 'auth_check']);
    Route::post('login', [AuthController::class, 'auth_login']);
    Route::post('logout', [AuthController::class, 'auth_logout']);
    Route::get('me', [AuthController::class, 'auth_me']);
    Route::post('refresh', [AuthController::class, 'auth_refresh']);
    Route::post('register', [AuthController::class, 'auth_register']);
});

Route::group(['middleware' => [JwtMiddleware::class]], function () {

});
