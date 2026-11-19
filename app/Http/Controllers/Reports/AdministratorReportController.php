<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\AdministratorDashboardService;
use App\Support\AcademicTermSelection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdministratorReportController extends Controller
{
    public function index(Request $request, AdministratorDashboardService $service): View
    {
        $terms = ['Term 1', 'Term 2', 'Term 3', 'Term 4'];
        $termIndex = AcademicTermSelection::fromRequest($request, $terms);

        return view('administrator.reports', [
            'dashboard' => $service->build($termIndex),
            'terms' => $terms,
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
