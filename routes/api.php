<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EroeffnungController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () 
{
    Route::apiResource('users', UserController::class);
    Route::get('eroeffnungen/open', [EroeffnungController::class, 'open']);
	Route::post('austritte', [AustrittController::class, 'store']);
});