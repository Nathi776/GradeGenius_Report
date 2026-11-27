<?php

namespace App\Http\Controllers;

use App\Services\ExamScheduleService;
use App\Services\ReportDataSource;
use App\Services\StudentDashboardService;
use App\Services\StudentReportService;
use App\Services\StudyStatsService;
use App\Support\AcademicTermSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        StudentReportService $reports,
        StudentDashboardService $dashboard,
        ExamScheduleService $exams,
        ReportDataSource $source,
        StudyStatsService $studyStats,
    ): View {
        $raw = $source->forStudent($request->user());
        $termIndex = AcademicTermSelection::fromRequest($request, $raw['terms']);
        $raw['student']['name'] = $request->user()->name;
        $report = $reports->build($raw, $termIndex);
        $dashboardData = $dashboard->build($report);
        $dashboardData['exams'] = $exams->upcomingForGrade($report['student']['grade'], array_column($report['subjects'], 'name', 'key'), null, 3);

        $stats = $studyStats->forStudent($request->user()->id);
        $dashboardData['study_stats'] = $stats + ['real_study_minutes' => $stats['total_minutes']];

        return view('reports.student-home', [
            'dashboard' => $dashboardData,
            'terms' => $raw['terms'],
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
