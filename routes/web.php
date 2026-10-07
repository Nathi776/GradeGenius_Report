<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Reports\AdministratorReportController;
use App\Http\Controllers\Reports\ParentReportController;
use App\Http\Controllers\Reports\StudentReportController;
use App\Http\Controllers\Reports\TeacherReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard.landing');

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])
    ->middleware('throttle:login')
    ->name('login.store');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegistration'])
    ->middleware('throttle:login')
    ->name('register.store');
Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::prefix('student')
        ->name('student.')
        ->middleware('role:student')
        ->group(function (): void {
            Route::get('/reports', [StudentReportController::class, 'index'])
                ->name('reports');
        });

    Route::prefix('parent')
        ->name('parent.')
        ->middleware('role:parent')
        ->group(function (): void {
            Route::get('/reports', [ParentReportController::class, 'index'])
                ->name('reports');
        });

    Route::prefix('teacher')
        ->name('teacher.')
        ->middleware('role:teacher')
        ->group(function (): void {
            Route::get('/reports', [TeacherReportController::class, 'index'])
                ->name('reports');
        });

    Route::prefix('administrator')
        ->name('administrator.')
        ->middleware('role:administrator')
        ->group(function (): void {
            Route::get('/reports', [AdministratorReportController::class, 'index'])
                ->name('reports');
        });
});
