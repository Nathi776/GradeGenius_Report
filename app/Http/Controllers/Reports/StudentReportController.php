<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class StudentReportController extends Controller
{
    public function index()
    {
        return view('student.reports', [
            'progress' => [
                ['term' => 'Term 1', 'mark' => 58],
                ['term' => 'Term 2', 'mark' => 64],
                ['term' => 'Term 3', 'mark' => 69],
                ['term' => 'Term 4', 'mark' => 74],
            ],
            'subjects' => [
                ['name' => 'Mathematics', 'mark' => 78, 'grade' => 'B'],
                ['name' => 'Physical Sciences', 'mark' => 72, 'grade' => 'B'],
                ['name' => 'English Home Language', 'mark' => 81, 'grade' => 'A'],
                ['name' => 'Life Sciences', 'mark' => 68, 'grade' => 'C'],
                ['name' => 'Geography', 'mark' => 75, 'grade' => 'B'],
            ],
        ]);
    }
}
