<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SalesPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes (no authentication required)
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected routes (requires Sanctum token)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Current user info
    Route::get('/user', fn ($request) => response()->json($request->user()));

    // Sales Pages CRUD
    Route::apiResource('sales-pages', SalesPageController::class);
});
