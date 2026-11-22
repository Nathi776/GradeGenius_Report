<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassAnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentPlannerController;
use App\Http\Controllers\ParentReportController;
use App\Http\Controllers\ParentStudentLinkController;
use App\Http\Controllers\Reports\AdministratorReportController;
use App\Http\Controllers\Reports\TeacherReportController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentGuardianLinkController;
use App\Http\Controllers\StudentPlannerController;
use App\Http\Controllers\StudentRecommendationsController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherMessageController;
use App\Http\Middleware\RecordStudentActivity;
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
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllRead');

    Route::prefix('student')
        ->name('student.')
        ->middleware(['role:student', RecordStudentActivity::class])
        ->group(function (): void {
            Route::get('/', StudentDashboardController::class)
                ->name('home');
            Route::post('/guardians', StudentGuardianLinkController::class)
                ->middleware('throttle:login')
                ->name('guardians.store');
            Route::get('/report', StudentReportController::class)
                ->name('report');
            Route::get('/recommendations', StudentRecommendationsController::class)
                ->name('recommendations');
            Route::get('/planner', [StudentPlannerController::class, 'show'])
                ->name('planner');
            Route::post('/planner', [StudentPlannerController::class, 'generate'])
                ->name('planner.generate');
            Route::post('/planner/session', [StudentPlannerController::class, 'storeSession'])
                ->name('planner.store');
            Route::post('/planner/{session}/start', [StudentPlannerController::class, 'start'])
                ->whereNumber('session')
                ->name('planner.start');
            Route::post('/planner/{session}/pause', [StudentPlannerController::class, 'pause'])
                ->whereNumber('session')
                ->name('planner.pause');
            Route::post('/planner/{session}/resume', [StudentPlannerController::class, 'resume'])
                ->whereNumber('session')
                ->name('planner.resume');
            Route::patch('/planner/{session}', [StudentPlannerController::class, 'update'])
                ->whereNumber('session')
                ->name('planner.update');
            Route::delete('/planner/{session}', [StudentPlannerController::class, 'destroy'])
                ->whereNumber('session')
                ->name('planner.destroy');
            Route::redirect('/reports', '/student/report')
                ->name('reports');
        });

    Route::prefix('parent')
        ->name('parent.')
        ->middleware('role:parent')
        ->group(function (): void {
            Route::get('/', ParentDashboardController::class)
                ->name('home');
            Route::post('/links/{student}/confirm', [ParentStudentLinkController::class, 'confirm'])
                ->whereNumber('student')
                ->name('links.confirm');
            Route::post('/links/{student}/reject', [ParentStudentLinkController::class, 'reject'])
                ->whereNumber('student')
                ->name('links.reject');
            Route::get('/planner', [ParentPlannerController::class, 'show'])
                ->name('planner');
            Route::get('/planner/recap', [ParentPlannerController::class, 'recap'])
                ->name('planner.recap');
            Route::post('/encouragements', [ParentPlannerController::class, 'sendEncouragement'])
                ->name('encouragements.store');
            Route::delete('/encouragements/{encouragement}', [ParentPlannerController::class, 'deleteEncouragement'])
                ->whereNumber('encouragement')
                ->name('encouragements.destroy');
            Route::get('/report/{child?}', ParentReportController::class)
                ->name('report');
            Route::get('/reports', ParentReportController::class)
                ->name('reports');
            Route::get('/messages/{message}', [TeacherMessageController::class, 'showForParent'])
                ->whereNumber('message')
                ->name('messages.show');
            Route::post('/messages/{message}/acknowledge', [TeacherMessageController::class, 'acknowledge'])
                ->whereNumber('message')
                ->middleware('throttle:20,1')
                ->name('messages.acknowledge');
            Route::get('/announcements/{announcement}', [ClassAnnouncementController::class, 'showForParent'])
                ->whereNumber('announcement')
                ->name('announcements.show');
        });

    Route::prefix('teacher')
        ->name('teacher.')
        ->middleware('role:teacher')
        ->group(function (): void {
            Route::get('/', TeacherDashboardController::class)
                ->name('home');
            Route::get('/reports', [TeacherReportController::class, 'index'])
                ->name('reports');
            Route::get('/messages', [TeacherMessageController::class, 'index'])
                ->name('messages.index');
            Route::get('/messages/create', [TeacherMessageController::class, 'create'])
                ->name('messages.create');
            Route::post('/messages', [TeacherMessageController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('messages.store');
            Route::get('/announcements', [ClassAnnouncementController::class, 'index'])
                ->name('announcements.index');
            Route::get('/announcements/create', [ClassAnnouncementController::class, 'create'])
                ->name('announcements.create');
            Route::post('/announcements', [ClassAnnouncementController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('announcements.store');
        });

    Route::prefix('administrator')
        ->name('administrator.')
        ->middleware('role:administrator')
        ->group(function (): void {
            Route::get('/', [AdministratorReportController::class, 'index'])
                ->name('home');
            Route::get('/reports', [AdministratorReportController::class, 'index'])
                ->name('reports');
        });
});
