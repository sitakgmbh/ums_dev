<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EroeffnungController;
use App\Http\Controllers\Api\MutationController;
use App\Http\Controllers\Api\AustrittController;
use App\Http\Controllers\Api\IncidentController;

Route::middleware('apiauthswitcher')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        
		Route::get('eroeffnungen/open', [EroeffnungController::class, 'open']);
		Route::patch('eroeffnungen/{id}', [EroeffnungController::class, 'update']);
        
		Route::post('mutationen', [MutationController::class, 'store']);
		Route::patch('mutationen/{id}', [MutationController::class, 'update']);

		Route::post('austritte', [AustrittController::class, 'store']);
		Route::patch('austritte/{id}', [AustrittController::class, 'update']);
		
		Route::post('incidents', [IncidentController::class, 'store']);
    });
});
