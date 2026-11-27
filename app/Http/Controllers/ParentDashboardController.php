<?php

namespace App\Http\Controllers;

use App\Exceptions\NoReportDataException;
use App\Services\ParentDashboardService;
use App\Services\ParentReportService;
use App\Services\ParentTelemetryService;
use App\Services\ReportDataSource;
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
        ReportDataSource $source,
    ): View {
        $verifiedChildren = $request->user()->linkedChildren()->get();
        $learner = null;

        if ($verifiedChildren->isNotEmpty()) {
            $terms = ['Term 1', 'Term 2', 'Term 3', 'Term 4'];
            $termIndex = AcademicTermSelection::fromRequest($request, $terms);

            $reportsByChild = [];
            $names = [];
            $childCards = [];

            // Every confirmed child is listed, with or without recorded marks.
            foreach ($verifiedChildren as $student) {
                $childKey = (string) $student->id;
                $names[$childKey] = $student->name;
                $raw = $source->forStudentOrNull($student);

                if ($raw === null) {
                    $childCards[$childKey] = ['name' => $student->name, 'has_marks' => false];

                    continue;
                }

                $reportsByChild[$childKey] = $reports->build($raw, null, $termIndex);
                $overall = $reportsByChild[$childKey]['overall'];
                $childCards[$childKey] = [
                    'name' => $student->name,
                    'has_marks' => true,
                    'grade' => $reportsByChild[$childKey]['student']['grade'],
                    'average' => $overall['current'],
                    'at_target' => $overall['at_target'],
                    'subject_count' => $overall['subject_count'],
                    'term' => $overall['current_term'],
                ];
            }

            $key = (string) ($request->query('child') ?? $request->query('child_id') ?? array_key_first($names));
            if (! isset($names[$key])) {
                $key = (string) array_key_first($names);
            }

            $selected = $verifiedChildren->firstWhere('id', (int) $key);
            $learner = $telemetry->forStudent($selected);

            $dashboardData = isset($reportsByChild[$key])
                ? $dashboard->build($reportsByChild[$key], $names, $key, $childCards)
                : $dashboard->buildWithoutMarks($names, $key, $childCards);
        } else {
            if (! $source->demoFallbackEnabled()) {
                throw new NoReportDataException('no_children');
            }

            $linked = DemoReportData::childrenForParent();
            $key = $request->query('child', array_key_first($linked));
            abort_unless(is_string($key) && isset($linked[$key]), 404);
            $terms = $linked[$key]['terms'];
            $termIndex = AcademicTermSelection::fromRequest($request, $terms);

            $reportsByChild = [];
            $names = [];
            $childCards = [];
            foreach ($linked as $childKey => $raw) {
                $report = $reports->build($raw, null, $termIndex);
                $reportsByChild[$childKey] = $report;
                $names[$childKey] = $report['student']['name'];
                $overall = $report['overall'];
                $childCards[$childKey] = [
                    'name' => $report['student']['name'],
                    'has_marks' => true,
                    'grade' => $report['student']['grade'],
                    'average' => $overall['current'],
                    'at_target' => $overall['at_target'],
                    'subject_count' => $overall['subject_count'],
                    'term' => $overall['current_term'],
                ];
            }

            $dashboardData = $dashboard->build($reportsByChild[$key], $names, $key, $childCards);
        }

        return view('reports.parent-home', [
            'dashboard' => $dashboardData,
            'learner' => $learner,
            'pendingChildren' => $request->user()->pendingChildren()->get(),
            'terms' => $terms,
            'selectedTerm' => AcademicTermSelection::value($termIndex),
        ]);
    }
}
