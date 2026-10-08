<?php

namespace App\Http\Controllers;

use App\Services\StudentReportService;
use App\Support\DemoReportData;

class StudentReportController extends Controller
{
    public function __invoke(StudentReportService $reports)
    {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $report = $reports->build(DemoReportData::forStudent());

        return view('reports.student', ['report' => $report]);
    }
}
