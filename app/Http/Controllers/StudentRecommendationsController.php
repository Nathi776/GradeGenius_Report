<?php

namespace App\Http\Controllers;

use App\Services\ExamScheduleService;
use App\Services\LearningRecommendationService;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentRecommendationsController extends Controller
{
    public function __invoke(
        Request $request,
        StudentReportService $reports,
        ExamScheduleService $exams,
        LearningRecommendationService $recommendations,
    ): View {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $raw = DemoReportData::forStudent();
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
