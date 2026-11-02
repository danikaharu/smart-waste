<?php

use App\Http\Controllers\Api\AiDetectionApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ReportApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

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

    // ML Waste Image Classification Proxy
    Route::post('/detect-waste', [AiDetectionApiController::class, 'detect']);

    // Dashboard Statistics
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
});
