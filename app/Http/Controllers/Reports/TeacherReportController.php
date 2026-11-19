<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Support\AcademicTermSelection;
use Illuminate\Http\Request;

class TeacherReportController extends Controller
{
    public function index(Request $request)
    {
        $terms = ['Term 1', 'Term 2', 'Term 3', 'Term 4'];
        $termIndex = AcademicTermSelection::fromRequest($request, $terms);
        $selectedTerm = AcademicTermSelection::value($termIndex);
        $termIndex ??= count($terms) - 1;

        return view('teacher.reports', [
            'progress' => [56, 61, 66, 71],
            'terms' => $terms,
            'selectedTerm' => $selectedTerm,
            'selectedTermIndex' => $termIndex,
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
