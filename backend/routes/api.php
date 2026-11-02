<?php

use App\Http\Controllers\Api\AiDetectionApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ReportApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication API Routes (Rate-limited to 10 attempts/minute)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Sanctum Authenticated API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // User Profile & Session
    Route::get('/user', [AuthApiController::class, 'profile']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Waste Reports
    Route::get('/reports', [ReportApiController::class, 'index']);
    Route::post('/reports', [ReportApiController::class, 'store']);
    Route::get('/reports/{id}', [ReportApiController::class, 'show']);

    // ML Waste Image Classification Proxy (Rate-limited to prevent abuse)
    Route::post('/detect-waste', [AiDetectionApiController::class, 'detect'])->middleware('throttle:20,1');

    // Dashboard Statistics
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
});
