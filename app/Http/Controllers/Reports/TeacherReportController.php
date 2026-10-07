<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class TeacherReportController extends Controller
{
    public function index()
    {
        return view('teacher.reports', [
            'progress' => [56, 61, 66, 71],
            'students' => [
                ['name' => 'Thabo M.', 'average' => 78, 'change' => 12],
                ['name' => 'Lerato N.', 'average' => 84, 'change' => 18],
                ['name' => 'Sipho K.', 'average' => 63, 'change' => 7],
                ['name' => 'Aisha P.', 'average' => 71, 'change' => 9],
                ['name' => 'Johan D.', 'average' => 59, 'change' => 4],
                ['name' => 'Naledi S.', 'average' => 82, 'change' => 15],
            ],
        ]);
    }
}
