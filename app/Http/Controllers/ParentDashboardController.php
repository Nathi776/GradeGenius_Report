<?php

namespace App\Http\Controllers;

use App\Services\ParentDashboardService;
use App\Services\ParentReportService;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ParentDashboardController extends Controller
{
    public function __invoke(Request $request, ParentReportService $reports, ParentDashboardService $dashboard): View
    {
        // TODO: use the signed-in parent's linked children from the database, and answer 404/403 for
        // any learner who is not linked to them. Never trust the `child` value on its own.
        $linked = DemoReportData::childrenForParent();
        $key = $request->query('child', array_key_first($linked));
        abort_unless(is_string($key) && isset($linked[$key]), 404);

        $names = array_map(fn (array $raw): string => $raw['student']['name'], $linked);

        return view('reports.parent-home', [
            'dashboard' => $dashboard->build($reports->build($linked[$key]), $names, $key),
        ]);
    }
}
