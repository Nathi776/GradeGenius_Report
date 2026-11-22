<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Services\ExamScheduleService;
use App\Services\StudentActivityTracker;
use App\Services\StudentDashboardService;
use App\Services\StudentReportService;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        StudentReportService $reports,
        StudentDashboardService $dashboard,
        StudentActivityTracker $activity,
        ExamScheduleService $exams,
    ): View {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $raw = DemoReportData::forStudent();
        $termIndex = AcademicTermSelection::fromRequest($request, $raw['terms']);
        $raw['student']['name'] = $request->user()->name;
        $report = $reports->build($raw, $termIndex);
        $dashboardData = $dashboard->build($report);
        $dashboardData['exams'] = $exams->upcomingForGrade($report['student']['grade'], array_column($report['subjects'], 'name', 'key'), null, 3);
        $dashboardData['telemetry'] = $activity->dashboardSnapshot($request, $request->user());
        $dashboardData['linked_parents'] = $request->user()->parents()
            ->where('role', 'parent')
            ->get(['users.name', 'users.email'])
            ->map(fn ($parent): array => ['name' => $parent->name, 'email' => $parent->email])
            ->all();

        // Calculate real study time, streak, and weekly goal
        $today = now()->startOfDay();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $realStudyMinutes = (int) StudySession::where('user_id', $request->user()->id)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true)
            ->sum('actual_minutes');
        $weeklyMinutes = (int) StudySession::where('user_id', $request->user()->id)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true)
            ->whereBetween('planned_date', [$weekStart, $weekEnd])
            ->sum('actual_minutes');

        $streak = 0;
        $checkDate = $today->copy();
        $todayHasDone = StudySession::where('user_id', $request->user()->id)
            ->whereDate('planned_date', $checkDate)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true)
            ->exists();
        if (! $todayHasDone) {
            $checkDate->subDay();
        }
        while (true) {
            $hasDone = StudySession::where('user_id', $request->user()->id)
                ->whereDate('planned_date', $checkDate)
                ->where('status', StudySession::STATUS_DONE)
                ->where('is_counted', true)
                ->exists();
            if (! $hasDone) {
                break;
            }
            $streak++;
            $checkDate->subDay();
        }

        $dashboardData['study_stats'] = [
            'streak' => $streak,
            'weekly_minutes' => $weeklyMinutes,
            'weekly_goal' => 630,
            'real_study_minutes' => $realStudyMinutes,
        ];

        return view('reports.student-home', [
            'dashboard' => $dashboardData,
            'terms' => $raw['terms'],
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
