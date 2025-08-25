<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    
    // Routes d'authentification
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Dashboard
    Route::get('dashboard', [PaymentController::class, 'dashboard']);
    
    // Statistiques
    Route::get('statistics', [PaymentController::class, 'statistics']);

    // Paiements
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('{payment}', [PaymentController::class, 'show']);
        Route::get('{payment}/receipt', [PaymentController::class, 'downloadReceipt']);
    });
});

Route::get('test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Payment API is working!',
        'timestamp' => now(),
    ]);
});