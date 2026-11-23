<?php

namespace App\Http\Controllers;

use App\Services\ReportDataSource;
use App\Services\StudentReportService;
use App\Support\AcademicTermSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentReportController extends Controller
{
    public function __invoke(Request $request, StudentReportService $reports, ReportDataSource $source): View
    {
        $raw = $source->forStudent($request->user());
        $termIndex = AcademicTermSelection::fromRequest($request, $raw['terms']);
        $report = $reports->build($raw, $termIndex);

        return view('reports.student', [
            'report' => $report,
            'terms' => $raw['terms'],
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
