<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EroeffnungController;
use App\Http\Controllers\Api\AustrittController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Im local-Modus erfolgt Authentifizierung via Sanctum-Token.
| Im SSO-Modus authentifiziert Apache und Laravel
| übernimmt den Benutzer automatisch aus REMOTE_USER.
|
*/

Route::post("/login", [AuthController::class, "login"]);

Route::middleware(['auth:sanctum', 'api.auth'])->group(function () 
{
    Route::get("/me", [AuthController::class, "me"]);

    Route::middleware("role:admin")->group(function () 
	{
        Route::apiResource("users", UserController::class);
        Route::get("eroeffnungen/open", [EroeffnungController::class, "open"]);
        Route::post("austritte", [AustrittController::class, "store"]);
    });
});
