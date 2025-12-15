<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

// ========== Authentication Routes (Public) ==========
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ========== Protected Routes (Require Authentication) ==========
Route::middleware('api.auth')->group(function () {
    
    // Auth routes (authenticated)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Routes untuk Services (CRUD lengkap)
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);           // GET semua
        Route::post('/', [ServiceController::class, 'store']);          // POST buat baru
        Route::get('/{id}', [ServiceController::class, 'show']);        // GET satu
        Route::put('/{id}', [ServiceController::class, 'update']);      // PUT update
        Route::delete('/{id}', [ServiceController::class, 'destroy']);  // DELETE hapus
    });

    // Routes untuk Bookings (CRUD lengkap)
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);           // GET semua
        Route::post('/', [BookingController::class, 'store']);          // POST buat baru
        Route::get('/{id}', [BookingController::class, 'show']);        // GET satu
        Route::put('/{id}', [BookingController::class, 'update']);      // PUT update
        Route::delete('/{id}', [BookingController::class, 'destroy']);  // DELETE hapus
    });
});