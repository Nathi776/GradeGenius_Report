<?php

use App\Services\StudentActivityTracker;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(StudentActivityTracker::class)->pruneExpired())->daily();

Schedule::command('app:prune-notifications')->daily();
