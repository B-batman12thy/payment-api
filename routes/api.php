<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;

/**
 * Routes publiques (pas de JWT)
 */
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

/**
 * Routes protégées (JWT requis)
 */
Route::middleware('auth:api')->group(function () {
    // Auth protégée
    Route::get('auth/me',       [AuthController::class, 'me']);
    Route::post('auth/logout',  [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    // Dashboard & stats
    Route::get('dashboard',  [PaymentController::class, 'dashboard']);
    Route::get('statistics', [PaymentController::class, 'statistics']);

    // Paiements
    Route::prefix('payments')->group(function () {
        Route::get('/',                 [PaymentController::class, 'index']);
        Route::post('/',                [PaymentController::class, 'store']);
        Route::get('{payment}',         [PaymentController::class, 'show']);
        Route::get('{payment}/receipt', [PaymentController::class, 'downloadReceipt']);
    });
});

/** Ping */
Route::get('test', fn () => response()->json([
    'success'   => true,
    'message'   => 'Payment API is working!',
    'timestamp' => now(),
]));
