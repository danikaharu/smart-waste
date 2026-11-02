<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes (Guarded by auth & role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'show'])->name('reports.show');
    Route::put('/reports/{id}/status', [\App\Http\Controllers\Admin\ReportController::class, 'updateStatus'])->name('reports.updateStatus');
    Route::delete('/reports/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'destroy'])->name('reports.destroy');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::put('/users/{id}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.updateRole');
    Route::put('/users/{id}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggleActive');
});

// Citizen User Routes (Guarded by auth & role:user)
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\User\UserReportController::class, 'index'])->name('dashboard');
    Route::get('/reports/create', [\App\Http\Controllers\User\UserReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [\App\Http\Controllers\User\UserReportController::class, 'store'])->name('reports.store');
});
