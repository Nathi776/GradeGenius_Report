<?php

namespace App\Http\Controllers;

use App\Services\StudentDashboardService;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request, StudentReportService $reports, StudentDashboardService $dashboard): View
    {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $raw = DemoReportData::forStudent();
        $raw['student']['name'] = $request->user()->name;
        $report = $reports->build($raw);

        return view('reports.student-home', ['dashboard' => $dashboard->build($report)]);
    }
}
