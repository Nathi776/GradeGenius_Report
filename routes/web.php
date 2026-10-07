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
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
    ->middleware('guest')
    ->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
    ->middleware(['guest', 'throttle:login'])
    ->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
    ->middleware('guest')
    ->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware(['guest', 'throttle:login'])
    ->name('password.update');
Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/email/verify', [AuthController::class, 'verificationNotice'])
    ->middleware('auth')
    ->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (): void {
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
