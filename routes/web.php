<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Reports\ParentReportController;
use App\Http\Controllers\Reports\StudentReportController;
use App\Http\Controllers\Reports\TeacherReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/reports', [StudentReportController::class, 'index'])
            ->name('reports');
    });

Route::prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/reports', [ParentReportController::class, 'index'])
            ->name('reports');
    });

Route::prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/reports', [TeacherReportController::class, 'index'])
            ->name('reports');
    });
