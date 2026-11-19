<?php

namespace App\Http\Controllers;

use App\Services\ParentDashboardService;
use App\Services\ParentReportService;
use App\Services\ParentTelemetryService;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ParentDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        ParentReportService $reports,
        ParentDashboardService $dashboard,
        ParentTelemetryService $telemetry,
    ): View {
        // TODO: use the signed-in parent's linked children from the database, and answer 404/403 for
        // any learner who is not linked to them. Never trust the `child` value on its own.
        $linked = DemoReportData::childrenForParent();
        $key = $request->query('child', array_key_first($linked));
        abort_unless(is_string($key) && isset($linked[$key]), 404);
        $terms = $linked[$key]['terms'];
        $termIndex = AcademicTermSelection::fromRequest($request, $terms);

        $reportsByChild = [];
        foreach ($linked as $childKey => $raw) {
            $reportsByChild[$childKey] = $reports->build($raw, null, $termIndex);
        }

        $names = [];
        $childCards = [];
        foreach ($reportsByChild as $childKey => $report) {
            $names[$childKey] = $report['student']['name'];
            $overall = $report['overall'];
            $childCards[$childKey] = [
                'name' => $report['student']['name'],
                'grade' => $report['student']['grade'],
                'average' => $overall['current'],
                'at_target' => $overall['at_target'],
                'subject_count' => $overall['subject_count'],
                'term' => $overall['current_term'],
            ];
        }

        $linkedLearners = $telemetry->forParent($request->user());
        $requestedLearnerId = $request->query('learner');
        if ($requestedLearnerId !== null) {
            $learnerId = filter_var($requestedLearnerId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($learnerId !== false, 404);
            $selectedLearner = collect($linkedLearners)->firstWhere('id', $learnerId);
            abort_if($selectedLearner === null, 404);
        } else {
            $selectedLearner = $linkedLearners[0] ?? null;
        }

        return view('reports.parent-home', [
            'dashboard' => $dashboard->build($reportsByChild[$key], $names, $key, $childCards),
            'linkedLearners' => $linkedLearners,
            'selectedLearner' => $selectedLearner,
            'terms' => $terms,
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
