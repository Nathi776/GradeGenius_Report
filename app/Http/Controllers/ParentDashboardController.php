<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Services\ParentDashboardService;
use App\Services\ParentReportService;
use App\Services\ParentTelemetryService;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Carbon\Carbon;
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
        $verifiedChildren = $request->user()->linkedChildren()->wherePivot('status', 'verified')->get();

        if ($verifiedChildren->isNotEmpty()) {
            $terms = ['Term 1', 'Term 2', 'Term 3', 'Term 4'];
            $termIndex = AcademicTermSelection::fromRequest($request, $terms);

            $reportsByChild = [];
            $names = [];
            $childCards = [];

            foreach ($verifiedChildren as $student) {
                $childKey = (string) $student->id;
                $raw = DemoReportData::forStudent();
                $raw['student']['name'] = $student->name;
                $reportsByChild[$childKey] = $reports->build($raw, null, $termIndex);
                $names[$childKey] = $student->name;

                $overall = $reportsByChild[$childKey]['overall'];
                $childCards[$childKey] = [
                    'name' => $student->name,
                    'grade' => $reportsByChild[$childKey]['student']['grade'],
                    'average' => $overall['current'],
                    'at_target' => $overall['at_target'],
                    'subject_count' => $overall['subject_count'],
                    'term' => $overall['current_term'],
                ];
            }

            $key = $request->query('child') ?? $request->query('child_id') ?? (string) $verifiedChildren->first()->id;
            if (! isset($reportsByChild[$key])) {
                $key = (string) $verifiedChildren->first()->id;
            }
        } else {
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
        }

        $linkedLearners = $telemetry->forParent($request->user());
        $requestedLearnerId = $request->query('learner') ?? $request->query('child_id');
        if ($requestedLearnerId !== null) {
            $learnerId = filter_var($requestedLearnerId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($learnerId !== false, 403, 'Invalid learner ID.');
            $selectedLearner = collect($linkedLearners)->firstWhere('id', $learnerId);
            abort_if($selectedLearner === null, 403, 'Unauthorized access to child.');
        } else {
            $selectedLearner = $linkedLearners[0] ?? null;
        }

        // Real study time, streak, and goal from study_sessions
        $today = now()->startOfDay();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $weeklyGoal = 630;

        foreach ($linkedLearners as &$l) {
            $lId = (int) $l['id'];
            $l['real_study_minutes'] = (int) StudySession::where('user_id', $lId)
                ->where('status', StudySession::STATUS_DONE)
                ->where('is_counted', true)
                ->sum('actual_minutes');
            $l['weekly_study_minutes'] = (int) StudySession::where('user_id', $lId)
                ->where('status', StudySession::STATUS_DONE)
                ->where('is_counted', true)
                ->whereBetween('planned_date', [$weekStart, $weekEnd])
                ->sum('actual_minutes');
            $l['streak'] = $this->calculateStreak($lId, $today);
        }
        unset($l);

        $realStudyMinutes = 0;
        $streak = 0;
        $weeklyMinutes = 0;

        if ($selectedLearner !== null) {
            $learnerId = (int) $selectedLearner['id'];
            $realStudyMinutes = (int) StudySession::where('user_id', $learnerId)
                ->where('status', StudySession::STATUS_DONE)
                ->where('is_counted', true)
                ->sum('actual_minutes');

            $weeklyMinutes = (int) StudySession::where('user_id', $learnerId)
                ->where('status', StudySession::STATUS_DONE)
                ->where('is_counted', true)
                ->whereBetween('planned_date', [$weekStart, $weekEnd])
                ->sum('actual_minutes');

            $streak = $this->calculateStreak($learnerId, $today);
        }

        return view('reports.parent-home', [
            'dashboard' => $dashboard->build($reportsByChild[$key], $names, $key, $childCards),
            'linkedLearners' => $linkedLearners,
            'selectedLearner' => $selectedLearner,
            'terms' => $terms,
            'selectedTerm' => AcademicTermSelection::value($termIndex),
            'realStudyStats' => [
                'total_minutes' => $realStudyMinutes,
                'weekly_minutes' => $weeklyMinutes,
                'weekly_goal' => $weeklyGoal,
                'streak' => $streak,
            ],
            'activeChildId' => $selectedLearner['id'] ?? null,
        ]);
    }

    private function calculateStreak(int $studentId, Carbon $today): int
    {
        $streak = 0;
        $checkDate = $today->copy();

        $todayHasDone = StudySession::where('user_id', $studentId)
            ->whereDate('planned_date', $checkDate)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true)
            ->exists();

        if (! $todayHasDone) {
            $checkDate->subDay();
        }

        while (true) {
            $hasDone = StudySession::where('user_id', $studentId)
                ->whereDate('planned_date', $checkDate)
                ->where('status', StudySession::STATUS_DONE)
                ->where('is_counted', true)
                ->exists();

            if (! $hasDone) {
                break;
            }

            $streak++;
            $checkDate->subDay();
        }

        return $streak;
    }
}
