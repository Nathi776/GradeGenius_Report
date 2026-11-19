<?php

namespace App\Http\Controllers;

use App\Services\StudentReportService;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentReportController extends Controller
{
    public function __invoke(Request $request, StudentReportService $reports): View
    {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $raw = DemoReportData::forStudent();
        $termIndex = AcademicTermSelection::fromRequest($request, $raw['terms']);
        $report = $reports->build($raw, $termIndex);

        return view('reports.student', [
            'report' => $report,
            'terms' => $raw['terms'],
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
