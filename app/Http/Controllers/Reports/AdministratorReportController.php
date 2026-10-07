<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdministratorReportController extends Controller
{
    public function index(): View
    {
        return view('administrator.reports');
    }
}
