<?php

namespace App\Http\Controllers;

use App\Models\ParentEncouragement;
use App\Models\StudySession;
use App\Services\ExamScheduleService;
use App\Services\LearningRecommendationService;
use App\Services\StudentReportService;
use App\Services\StudyPlannerService;
use App\Support\DemoReportData;
use Carbon\Carbon;
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
            ->map(function ($group, string $date) use ($today, $subjectNames): array {
                $plannedDate = $group->first()->planned_date->startOfDay();
                $isToday = $date === $today->toDateString();
                $isFuture = $plannedDate->gt($today);
                $isPast = $plannedDate->lt($today);

                return [
                    'date' => $date,
                    'label' => $isToday ? 'Today' : $plannedDate->format('D j M'),
                    'is_today' => $isToday,
                    'is_future' => $isFuture,
                    'is_past' => $isPast,
                    'sessions' => $group->map(function (StudySession $session) use ($subjectNames, $isFuture): array {
                        $minMinutes = $session->minimumMinutes();

                        // calculate active elapsed seconds if started
                        $activeSeconds = 0;
                        if ($session->started_at) {
                            $endTime = $session->finished_at ?? ($session->is_paused ? $session->paused_at : now());
                            $totalSecs = max(0, $session->started_at->diffInSeconds($endTime));
                            $activeSeconds = max(0, $totalSecs - ($session->paused_seconds ?? 0));
                        }

                        return [
                            'id' => $session->id,
                            'subject_key' => $session->subject_key,
                            'subject' => $subjectNames[$session->subject_key] ?? ucwords(str_replace('-', ' ', $session->subject_key)),
                            'topic' => $session->topic,
                            'minutes' => $session->minutes,
                            'minimum_minutes' => $minMinutes,
                            'status' => $session->status,
                            'is_future' => $isFuture,
                            'started_at' => $session->started_at?->toISOString(),
                            'is_paused' => (bool) $session->is_paused,
                            'paused_seconds' => (int) ($session->paused_seconds ?? 0),
                            'actual_minutes' => (int) ($session->actual_minutes ?? 0),
                            'is_counted' => (bool) $session->is_counted,
                            'active_seconds' => $activeSeconds,
                        ];
                    })->all(),
                ];
            })->values()->all();

        // Progress calculations
        $todaySessions = $sessions->filter(fn (StudySession $s) => $s->planned_date->toDateString() === $today->toDateString());
        $todayTotal = $todaySessions->count();
        $todayDone = $todaySessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->count();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $thisWeekSessions = $sessions->filter(fn (StudySession $s) => $s->planned_date->between($weekStart, $weekEnd));
        $thisWeekTotal = $thisWeekSessions->count();
        $thisWeekDone = $thisWeekSessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->count();

        $minutesDone = $thisWeekSessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->sum('actual_minutes');
        $minutesPlanned = $thisWeekSessions->sum('minutes');

        $upcoming = $sessions->filter(fn (StudySession $s): bool => $s->planned_date->gte($today));

        // Latest parent encouragement for this student
        $latestEncouragement = ParentEncouragement::where('student_id', $request->user()->id)
            ->with('parent')
            ->latest()
            ->first();

        return view('reports.student-planner', [
            'student' => $report['student'],
            'days' => $days,
            'subjects' => $report['subjects'],
            'summary' => [
                'planned' => $upcoming->where('status', StudySession::STATUS_PLANNED)->count(),
                'done_this_week' => $thisWeekDone,
                'minutes_planned' => $upcoming->where('status', StudySession::STATUS_PLANNED)->sum('minutes'),
            ],
            'progress' => [
                'today_done' => $todayDone,
                'today_total' => $todayTotal,
                'week_done' => $thisWeekDone,
                'week_total' => $thisWeekTotal,
                'minutes_done' => $minutesDone,
                'minutes_planned' => $minutesPlanned,
            ],
            'rules' => [
                'session_minutes' => StudyPlannerService::SESSION_MINUTES,
                'daily_minutes' => StudyPlannerService::DAILY_MINUTES,
                'plan_days' => StudyPlannerService::PLAN_DAYS,
            ],
            'encouragement' => $latestEncouragement,
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

        // Lock completed, skipped, and too_short sessions
        $lockedTopics = $request->user()->studySessions()
            ->whereIn('status', [StudySession::STATUS_DONE, StudySession::STATUS_SKIPPED, StudySession::STATUS_TOO_SHORT])
            ->pluck('topic')
            ->all();

        // Filter out items already locked so we only re-plan remaining topics
        $remainingItems = array_values(array_filter(
            $items,
            fn (array $item) => ! in_array($item['topic'], $lockedTopics, true)
        ));

        $plan = $planner->build($remainingItems, $upcoming, $today);

        // Delete only uncompleted planned sessions from today onwards
        $request->user()->studySessions()
            ->where('status', StudySession::STATUS_PLANNED)
            ->whereDate('planned_date', '>=', $today)
            ->delete();

        foreach ($plan as $row) {
            $request->user()->studySessions()->create($row);
        }

        if ($plan === []) {
            return redirect()->route('student.planner')->with('status', 'All topics are already completed or skipped. Nothing new to plan.');
        }

        return redirect()->route('student.planner')->with('status', count($plan).' revision sessions planned.');
    }

    public function storeSession(Request $request, StudentReportService $reports): RedirectResponse
    {
        $report = $this->reportFor($request, $reports);
        $validKeys = array_column($report['subjects'], 'key');

        $validated = $request->validate([
            'subject_key' => ['required', 'string', Rule::in($validKeys)],
            'topic' => ['required', 'string', 'max:120'],
            'minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60])],
            'planned_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $request->user()->studySessions()->create([
            'subject_key' => $validated['subject_key'],
            'topic' => $validated['topic'],
            'minutes' => $validated['minutes'],
            'planned_date' => $validated['planned_date'],
            'status' => StudySession::STATUS_PLANNED,
        ]);

        return redirect()->route('student.planner')->with('status', "Session '{$validated['topic']}' added successfully.");
    }

    public function start(Request $request, int $session): RedirectResponse
    {
        $study = $request->user()->studySessions()->findOrFail($session);
        $today = now()->startOfDay();

        if ($study->planned_date->gt($today)) {
            return redirect()->route('student.planner')->with('error', 'Future sessions cannot be started before their scheduled date.');
        }

        if ($study->status !== StudySession::STATUS_PLANNED) {
            return redirect()->route('student.planner')->with('error', 'Only planned sessions can be started.');
        }

        $study->update([
            'started_at' => now(),
            'is_paused' => false,
            'paused_at' => null,
            'paused_seconds' => 0,
        ]);

        return redirect()->route('student.planner')->with('status', "Started timer for '{$study->topic}'.");
    }

    public function pause(Request $request, int $session): RedirectResponse
    {
        $study = $request->user()->studySessions()->findOrFail($session);

        if (! $study->started_at || $study->status !== StudySession::STATUS_PLANNED) {
            return redirect()->route('student.planner');
        }

        if (! $study->is_paused) {
            $study->update([
                'is_paused' => true,
                'paused_at' => now(),
            ]);
        }

        return redirect()->route('student.planner')->with('status', "Timer paused for '{$study->topic}'.");
    }

    public function resume(Request $request, int $session): RedirectResponse
    {
        $study = $request->user()->studySessions()->findOrFail($session);

        if (! $study->started_at || $study->status !== StudySession::STATUS_PLANNED) {
            return redirect()->route('student.planner');
        }

        if ($study->is_paused && $study->paused_at) {
            $additionalPaused = max(0, $study->paused_at->diffInSeconds(now()));
            $study->update([
                'is_paused' => false,
                'paused_at' => null,
                'paused_seconds' => ($study->paused_seconds ?? 0) + $additionalPaused,
            ]);
        }

        return redirect()->route('student.planner')->with('status', "Timer resumed for '{$study->topic}'.");
    }

    public function update(Request $request, int $session): RedirectResponse
    {
        $study = $request->user()->studySessions()->findOrFail($session);
        $today = now()->startOfDay();

        $request->validate([
            'status' => ['nullable', 'string', Rule::in([StudySession::STATUS_PLANNED, StudySession::STATUS_SKIPPED, StudySession::STATUS_DONE, 'reschedule'])],
            'action' => ['nullable', 'string', Rule::in([StudySession::STATUS_PLANNED, StudySession::STATUS_SKIPPED, StudySession::STATUS_DONE, 'reschedule'])],
        ]);

        $action = $request->input('action') ?? $request->input('status');

        if ($action === 'reschedule') {
            $newDate = $request->input('reschedule_date') ? Carbon::parse($request->input('reschedule_date'))->toDateString() : $today->toDateString();
            $study->update([
                'status' => StudySession::STATUS_PLANNED,
                'planned_date' => $newDate,
                'started_at' => null,
                'finished_at' => null,
                'is_paused' => false,
                'paused_at' => null,
                'paused_seconds' => 0,
                'actual_minutes' => 0,
                'is_counted' => false,
                'completed_at' => null,
            ]);

            return redirect()->route('student.planner')->with('status', "Session rescheduled for {$newDate}.");
        }

        if ($action === 'planned') {
            // Undo action
            $study->update([
                'status' => StudySession::STATUS_PLANNED,
                'started_at' => null,
                'finished_at' => null,
                'is_paused' => false,
                'paused_at' => null,
                'paused_seconds' => 0,
                'actual_minutes' => 0,
                'is_counted' => false,
                'completed_at' => null,
            ]);

            return redirect()->route('student.planner')->with('status', "Session '{$study->topic}' reset to planned.");
        }

        // If trying to complete or skip, future sessions cannot be completed before their date
        if ($study->planned_date->gt($today)) {
            return redirect()->route('student.planner')->with('error', 'Future sessions cannot be completed or skipped before their scheduled date.');
        }

        if ($action === StudySession::STATUS_SKIPPED) {
            $study->update([
                'status' => StudySession::STATUS_SKIPPED,
                'is_counted' => false,
                'completed_at' => null,
                'finished_at' => now(),
            ]);

            return redirect()->route('student.planner')->with('status', "Session '{$study->topic}' marked as skipped.");
        }

        if ($action === StudySession::STATUS_DONE) {
            if (! $study->started_at) {
                return redirect()->route('student.planner')->with('error', 'You must start the session timer before completing it.');
            }

            // Calculate active seconds
            $endTime = now();
            $totalSecs = max(0, $study->started_at->diffInSeconds($endTime));
            $pausedSecs = $study->paused_seconds ?? 0;
            if ($study->is_paused && $study->paused_at) {
                $pausedSecs += max(0, $study->paused_at->diffInSeconds($endTime));
            }
            $activeSeconds = max(0, $totalSecs - $pausedSecs);
            $actualMinutes = (int) floor($activeSeconds / 60);
            $minimumMinutes = $study->minimumMinutes();

            // Check minimum required time (80% of planned minutes)
            if ($actualMinutes < $minimumMinutes) {
                $study->update([
                    'status' => StudySession::STATUS_TOO_SHORT,
                    'finished_at' => now(),
                    'actual_minutes' => max(1, $actualMinutes),
                    'is_counted' => false,
                    'completed_at' => null,
                ]);

                return redirect()->route('student.planner')->with('error', "You only studied for {$actualMinutes} minutes. You need {$minimumMinutes} minutes to complete this session.");
            }

            $study->update([
                'status' => StudySession::STATUS_DONE,
                'finished_at' => now(),
                'actual_minutes' => $actualMinutes,
                'is_counted' => true,
                'completed_at' => now(),
            ]);

            return redirect()->route('student.planner')->with('status', "Awesome! You completed '{$study->topic}' ({$actualMinutes} mins).");
        }

        return redirect()->route('student.planner');
    }

    public function destroy(Request $request, int $session): RedirectResponse
    {
        $request->user()->studySessions()->findOrFail($session)->delete();

        return redirect()->route('student.planner')->with('status', 'Study session removed.');
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
