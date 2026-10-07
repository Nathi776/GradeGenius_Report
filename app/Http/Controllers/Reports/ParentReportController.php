<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class ParentReportController extends Controller
{
    public function index()
    {
        return view('parent.reports', [
            'children' => [
                'Thabo M.' => [
                    'grade' => 'Grade 11',
                    'progress' => [55, 62, 68, 73],
                    'subjects' => [
                        ['name' => 'Mathematics', 'mark' => 76, 'grade' => 'B'],
                        ['name' => 'Physical Sciences', 'mark' => 70, 'grade' => 'B'],
                        ['name' => 'English', 'mark' => 79, 'grade' => 'A'],
                        ['name' => 'Life Sciences', 'mark' => 66, 'grade' => 'C'],
                    ],
                ],
                'Lerato M.' => [
                    'grade' => 'Grade 9',
                    'progress' => [61, 67, 72, 78],
                    'subjects' => [
                        ['name' => 'Mathematics', 'mark' => 80, 'grade' => 'A'],
                        ['name' => 'Natural Sciences', 'mark' => 74, 'grade' => 'B'],
                        ['name' => 'English', 'mark' => 82, 'grade' => 'A'],
                        ['name' => 'Social Sciences', 'mark' => 77, 'grade' => 'B'],
                    ],
                ],
            ],
        ]);
    }
}
