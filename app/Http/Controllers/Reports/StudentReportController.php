<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class StudentReportController extends Controller
{
    public function index()
    {
        return view('student.reports');
    }
}
