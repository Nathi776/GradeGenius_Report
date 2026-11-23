<?php

namespace App\Http\Controllers;

use App\Exceptions\NoReportDataException;
use App\Services\ParentReportService;
use App\Services\ReportDataSource;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Illuminate\Http\Request;

class ParentReportController extends Controller
{
    public function __invoke(Request $request, ParentReportService $reports, ReportDataSource $source, ?string $child = null)
    {
        $linkedChildren = $request->user()->linkedChildren()->get();

        $children = [];
        foreach ($linkedChildren as $student) {
            $raw = $source->forStudentOrNull($student);
            if ($raw !== null) {
                $children[(string) $student->id] = $raw;
            }
        }

        if ($children === []) {
            if (! $source->demoFallbackEnabled()) {
                throw new NoReportDataException($linkedChildren->isEmpty() ? 'no_children' : 'no_marks');
            }

            $children = DemoReportData::childrenForParent();
        }

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
