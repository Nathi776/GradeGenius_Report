<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class TeacherReportController extends Controller
{
    public function index()
    {
        return view('teacher.reports');
    }
}
