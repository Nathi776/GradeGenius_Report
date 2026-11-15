<?php

namespace App\Http\Controllers;

use App\Services\TeacherDashboardService;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function __invoke(Request $request, TeacherDashboardService $dashboard): View
    {
        // TODO: load the signed-in teacher's assigned classes and enrolled learners from the database.
        // A teacher must only ever see classes and learners they are authorised for.
        $data = DemoReportData::classesForTeacher();
        $data['teacher']['name'] = $request->user()->name;

        return view('reports.teacher-home', ['dashboard' => $dashboard->build($data)]);
    }
}
