<?php

namespace App\Http\Controllers;

use App\Services\ParentReportService;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Illuminate\Http\Request;

class ParentReportController extends Controller
{
    public function __invoke(Request $request, ParentReportService $reports, ?string $child = null)
    {
        // TODO: use the signed-in parent's linked children from the database, and answer 404/403 for
        // any learner who is not linked to them. Never trust the {child} value on its own.
        $children = DemoReportData::childrenForParent();
        $key = $child ?? array_key_first($children);
        abort_unless(isset($children[$key]), 404);
        $terms = $children[$key]['terms'];
        $termIndex = AcademicTermSelection::fromRequest($request, $terms);

        return view('reports.parent', [
            'report' => $reports->build($children[$key], null, $termIndex),
            'children' => array_map(fn ($raw) => $raw['student']['name'], $children),
            'activeChild' => $key,
            'terms' => $terms,
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
