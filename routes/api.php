<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EroeffnungController;
use App\Http\Controllers\Api\MutationController;
use App\Http\Controllers\Api\AustrittController;

Route::middleware('apiauthswitcher')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('eroeffnungen/open', [EroeffnungController::class, 'open']);
        Route::post('mutationen', [MutationController::class, 'store']);
		Route::post('austritte', [AustrittController::class, 'store']);
    });
});
