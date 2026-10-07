<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class ParentReportController extends Controller
{
    public function index()
    {
        return view('parent.reports');
    }
}
