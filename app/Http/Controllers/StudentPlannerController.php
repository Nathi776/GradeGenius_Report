<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Services\ExamScheduleService;
use App\Services\LearningRecommendationService;
use App\Services\StudentReportService;
use App\Services\StudyPlannerService;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentPlannerController extends Controller
{
    public function show(Request $request, StudentReportService $reports): View
    {
        $report = $this->reportFor($request, $reports);
        $subjectNames = array_column($report['subjects'], 'name', 'key');
        $today = now()->startOfDay();

        $sessions = $request->user()->studySessions()
            ->whereDate('planned_date', '>=', $today->copy()->subDays(7))
            ->orderBy('planned_date')
            ->orderBy('id')
            ->get();

        $days = $sessions->groupBy(fn (StudySession $session): string => $session->planned_date->toDateString())
            ->map(fn ($group, string $date): array => [
                'date' => $date,
                'label' => $date === $today->toDateString() ? 'Today' : $group->first()->planned_date->format('D j M'),
                'is_today' => $date === $today->toDateString(),
                'is_past' => $date < $today->toDateString(),
                'sessions' => $group->map(fn (StudySession $session): array => [
                    'id' => $session->id,
                    'subject' => $subjectNames[$session->subject_key] ?? ucwords(str_replace('-', ' ', $session->subject_key)),
                    'topic' => $session->topic,
                    'minutes' => $session->minutes,
                    'status' => $session->status,
                ])->all(),
            ])->values()->all();

        $upcoming = $sessions->filter(fn (StudySession $session): bool => $session->planned_date->gte($today));
        $thisWeek = $sessions->filter(fn (StudySession $session): bool => $session->planned_date->between($today->copy()->startOfWeek(), $today->copy()->endOfWeek()));

        return view('reports.student-planner', [
            'student' => $report['student'],
            'days' => $days,
            'summary' => [
                'planned' => $upcoming->where('status', StudySession::STATUS_PLANNED)->count(),
                'done_this_week' => $thisWeek->where('status', StudySession::STATUS_DONE)->count(),
                'minutes_planned' => $upcoming->where('status', StudySession::STATUS_PLANNED)->sum('minutes'),
            ],
            'rules' => [
                'session_minutes' => StudyPlannerService::SESSION_MINUTES,
                'daily_minutes' => StudyPlannerService::DAILY_MINUTES,
                'plan_days' => StudyPlannerService::PLAN_DAYS,
            ],
            'hideWorkspaceTermSelector' => true,
        ]);
    }

    public function generate(
        Request $request,
        StudentReportService $reports,
        ExamScheduleService $exams,
        LearningRecommendationService $recommendations,
        StudyPlannerService $planner,
    ): RedirectResponse {
        $report = $this->reportFor($request, $reports);
        $upcoming = $exams->upcomingForGrade($report['student']['grade'], array_column($report['subjects'], 'name', 'key'), null, 20);
        $items = $recommendations->build($report, $upcoming)['items'];
        $today = now()->startOfDay();

        $plan = $planner->build($items, $upcoming, $today);

        $request->user()->studySessions()
            ->where('status', StudySession::STATUS_PLANNED)
            ->whereDate('planned_date', '>=', $today)
            ->delete();

        foreach ($plan as $row) {
            $request->user()->studySessions()->create($row);
        }

        if ($plan === []) {
            return redirect()->route('student.planner')->with('status', 'No topics need revision right now, so nothing was planned.');
        }

        return redirect()->route('student.planner')->with('status', count($plan).' revision sessions planned.');
    }

    public function update(Request $request, int $session): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                StudySession::STATUS_PLANNED,
                StudySession::STATUS_DONE,
                StudySession::STATUS_SKIPPED,
            ])],
        ]);

        $study = $request->user()->studySessions()->findOrFail($session);
        $study->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === StudySession::STATUS_DONE ? now() : null,
        ]);

        return redirect()->route('student.planner');
    }

    public function destroy(Request $request, int $session): RedirectResponse
    {
        $request->user()->studySessions()->findOrFail($session)->delete();

        return redirect()->route('student.planner');
    }

    /**
     * @return array<string, mixed>
     */
    private function reportFor(Request $request, StudentReportService $reports): array
    {
        // TODO: replace DemoReportData with the signed-in learner's data from the database.
        $raw = DemoReportData::forStudent();
        $raw['student']['name'] = $request->user()->name;

        return $reports->build($raw, count($raw['terms']) - 1);
    }
}
