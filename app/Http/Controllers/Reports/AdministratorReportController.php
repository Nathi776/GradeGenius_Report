<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\AdministratorDashboardService;
use Illuminate\View\View;

class AdministratorReportController extends Controller
{
    public function index(AdministratorDashboardService $service): View
    {
        return view('administrator.reports', [
            'dashboard' => $service->build(),
        ]);
    }
}
