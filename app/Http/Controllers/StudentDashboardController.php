<?php

namespace App\Http\Controllers;

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
    ): View {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $raw = DemoReportData::forStudent();
        $termIndex = AcademicTermSelection::fromRequest($request, $raw['terms']);
        $raw['student']['name'] = $request->user()->name;
        $report = $reports->build($raw, $termIndex);
        $dashboardData = $dashboard->build($report);
        $dashboardData['telemetry'] = $activity->dashboardSnapshot($request, $request->user());
        $dashboardData['linked_parents'] = $request->user()->parents()
            ->where('role', 'parent')
            ->get(['users.name', 'users.email'])
            ->map(fn ($parent): array => ['name' => $parent->name, 'email' => $parent->email])
            ->all();

        return view('reports.student-home', [
            'dashboard' => $dashboardData,
            'terms' => $raw['terms'],
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
