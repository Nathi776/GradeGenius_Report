<?php

namespace App\Http\Controllers;

use App\Services\ExamScheduleService;
use App\Services\LearningRecommendationService;
use App\Services\ReportDataSource;
use App\Services\StudentReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentRecommendationsController extends Controller
{
    public function __invoke(
        Request $request,
        StudentReportService $reports,
        ExamScheduleService $exams,
        LearningRecommendationService $recommendations,
        ReportDataSource $source,
    ): View {
        $raw = $source->forStudent($request->user());
        $raw['student']['name'] = $request->user()->name;
        $report = $reports->build($raw, count($raw['terms']) - 1);
        $upcoming = $exams->upcomingForGrade($report['student']['grade'], array_column($report['subjects'], 'name', 'key'));

        return view('reports.student-recommendations', [
            'student' => $report['student'],
            'exams' => $upcoming,
            'plan' => $recommendations->build($report, $upcoming),
            'hideWorkspaceTermSelector' => true,
        ]);
    }
}
