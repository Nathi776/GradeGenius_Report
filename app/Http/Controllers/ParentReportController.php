<?php

namespace App\Http\Controllers;

use App\Services\ParentReportService;
use App\Support\DemoReportData;

class ParentReportController extends Controller
{
    public function __invoke(ParentReportService $reports, ?string $child = null)
    {
        // TODO: use the signed-in parent's linked children from the database, and answer 404/403 for
        // any learner who is not linked to them. Never trust the {child} value on its own.
        $children = DemoReportData::childrenForParent();
        $key = $child ?? array_key_first($children);
        abort_unless(isset($children[$key]), 404);

        return view('reports.parent', [
            'report' => $reports->build($children[$key]),
            'children' => array_map(fn ($raw) => $raw['student']['name'], $children),
            'activeChild' => $key,
        ]);
    }
}
