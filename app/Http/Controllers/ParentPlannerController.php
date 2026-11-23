<?php

namespace App\Http\Controllers;

use App\Models\ParentEncouragement;
use App\Models\StudySession;
use App\Models\User;
use App\Services\ReportDataSource;
use App\Services\StudentReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParentPlannerController extends Controller
{
    public function show(Request $request, StudentReportService $reports, ReportDataSource $source): View
    {
        $children = $request->user()->linkedChildren()->wherePivot('status', 'verified')->get();

        $requestedChildId = $request->query('child_id') ?? $request->query('child');
        if ($requestedChildId !== null) {
            $child = is_numeric($requestedChildId) ? User::find((int) $requestedChildId) : null;
            abort_unless($child && $request->user()->can('viewPlanner', $child), 403, 'Unauthorized access to child study planner.');
            $selectedChild = $child;
        } else {
            $selectedChild = $children->first();
        }

        if (! $selectedChild) {
            $hasPending = $request->user()->linkedChildren()->wherePivot('status', '!=', 'verified')->exists();

            return view('parent.planner', [
                'children' => collect(),
                'selectedChild' => null,
                'hasPending' => $hasPending,
                'days' => [],
                'stats' => [],
                'encouragements' => collect(),
            ]);
        }

        $today = now()->startOfDay();
        $sessions = StudySession::where('user_id', $selectedChild->id)
            ->whereDate('planned_date', '>=', $today->copy()->subDays(7))
            ->orderBy('planned_date')
            ->orderBy('id')
            ->get();

        // Subject names from student report data
        $rawReport = $source->forStudentOrNull($selectedChild);
        $subjectNames = $rawReport === null ? [] : array_column($reports->build($rawReport, 3)['subjects'], 'name', 'key');

        $days = $sessions->groupBy(fn (StudySession $s): string => $s->planned_date->toDateString())
            ->map(function ($group, string $date) use ($today, $subjectNames): array {
                $plannedDate = $group->first()->planned_date->startOfDay();
                $isToday = $date === $today->toDateString();
                $isFuture = $plannedDate->gt($today);
                $isPast = $plannedDate->lt($today);

                return [
                    'date' => $date,
                    'label' => $isToday ? 'TODAY' : strtoupper($plannedDate->format('D j M')),
                    'is_today' => $isToday,
                    'is_future' => $isFuture,
                    'is_past' => $isPast,
                    'sessions' => $group->map(fn (StudySession $s): array => [
                        'id' => $s->id,
                        'subject_key' => $s->subject_key,
                        'subject' => $subjectNames[$s->subject_key] ?? ucwords(str_replace('-', ' ', $s->subject_key)),
                        'topic' => $s->topic,
                        'minutes' => $s->minutes,
                        'status' => $s->status,
                        'is_counted' => (bool) $s->is_counted,
                        'actual_minutes' => (int) $s->actual_minutes,
                        'is_future' => $isFuture,
                    ])->all(),
                ];
            })->values()->all();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $thisWeekSessions = $sessions->filter(fn (StudySession $s) => $s->planned_date->between($weekStart, $weekEnd));
        $upcomingSessions = $sessions->filter(fn (StudySession $s) => $s->planned_date->gte($today));

        $doneThisWeek = $thisWeekSessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->count();
        $skippedCount = $thisWeekSessions->where('status', StudySession::STATUS_SKIPPED)->count();
        $plannedCount = $upcomingSessions->where('status', StudySession::STATUS_PLANNED)->count();
        $minutesPlanned = $thisWeekSessions->sum('minutes');
        $minutesCompleted = $thisWeekSessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->sum('actual_minutes');
        $minutesRemaining = max(0, $minutesPlanned - $minutesCompleted);

        // Calculate study streak
        $streak = $this->calculateStreak($selectedChild->id, $today);

        $encouragements = ParentEncouragement::where('student_id', $selectedChild->id)
            ->with('parent')
            ->latest()
            ->limit(5)
            ->get();

        return view('parent.planner', [
            'children' => $children,
            'selectedChild' => $selectedChild,
            'hasPending' => false,
            'days' => $days,
            'stats' => [
                'planned' => $plannedCount,
                'done_this_week' => $doneThisWeek,
                'skipped' => $skippedCount,
                'minutes_planned' => $minutesPlanned,
                'minutes_completed' => $minutesCompleted,
                'minutes_remaining' => $minutesRemaining,
                'streak' => $streak,
                'weekly_goal' => 630,
            ],
            'encouragements' => $encouragements,
        ]);
    }

    public function recap(Request $request, StudentReportService $reports, ReportDataSource $source): View
    {
        $children = $request->user()->linkedChildren()->wherePivot('status', 'verified')->get();

        $requestedChildId = $request->query('child_id') ?? $request->query('child');
        if ($requestedChildId !== null) {
            $child = is_numeric($requestedChildId) ? User::find((int) $requestedChildId) : null;
            abort_unless($child && $request->user()->can('viewPlanner', $child), 403, 'Unauthorized access to child study planner.');
            $selectedChild = $child;
        } else {
            $selectedChild = $children->first();
            abort_if($selectedChild === null, 403, 'No verified linked children found.');
        }

        $weekParam = $request->query('week');
        $weekDate = $weekParam ? Carbon::parse($weekParam)->startOfWeek() : now()->startOfWeek();
        $weekStart = $weekDate->copy()->startOfWeek();
        $weekEnd = $weekDate->copy()->endOfWeek();

        $sessions = StudySession::where('user_id', $selectedChild->id)
            ->whereBetween('planned_date', [$weekStart, $weekEnd])
            ->orderBy('planned_date')
            ->orderBy('id')
            ->get();

        $rawReport = $source->forStudentOrNull($selectedChild);
        $subjectNames = $rawReport === null ? [] : array_column($reports->build($rawReport, 3)['subjects'], 'name', 'key');

        $plannedCount = $sessions->count();
        $doneCount = $sessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->count();
        $skippedCount = $sessions->where('status', StudySession::STATUS_SKIPPED)->count();
        $tooShortCount = $sessions->where('status', StudySession::STATUS_TOO_SHORT)->count();

        $minutesPlanned = $sessions->sum('minutes');
        $minutesCompleted = $sessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->sum('actual_minutes');
        $completionPct = $minutesPlanned > 0 ? (int) round(($minutesCompleted / $minutesPlanned) * 100) : 0;

        // Breakdown Mon-Sun
        $daysBreakdown = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dayDate = $day->toDateString();
            $daySessions = $sessions->filter(fn (StudySession $s) => $s->planned_date->toDateString() === $dayDate);

            $daysBreakdown[] = [
                'day_name' => $day->format('l'),
                'date_label' => $day->format('j M'),
                'total' => $daySessions->count(),
                'done' => $daySessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->count(),
                'minutes_done' => $daySessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->sum('actual_minutes'),
                'sessions' => $daySessions->map(fn (StudySession $s) => [
                    'topic' => $s->topic,
                    'subject' => $subjectNames[$s->subject_key] ?? ucwords(str_replace('-', ' ', $s->subject_key)),
                    'status' => $s->status,
                    'is_counted' => (bool) $s->is_counted,
                    'actual_minutes' => $s->actual_minutes,
                ])->values()->all(),
            ];
        }

        // Per-subject breakdown
        $subjectBreakdown = [];
        foreach ($sessions->groupBy('subject_key') as $subKey => $subSessions) {
            $name = $subjectNames[$subKey] ?? ucwords(str_replace('-', ' ', $subKey));
            $subjectBreakdown[] = [
                'subject' => $name,
                'planned_count' => $subSessions->count(),
                'done_count' => $subSessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->count(),
                'minutes_completed' => $subSessions->where('status', StudySession::STATUS_DONE)->where('is_counted', true)->sum('actual_minutes'),
            ];
        }

        // Last 5 encouragements for that week
        $encouragements = ParentEncouragement::where('student_id', $selectedChild->id)
            ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
            ->with('parent')
            ->latest()
            ->limit(5)
            ->get();

        return view('parent.planner-recap', [
            'children' => $children,
            'selectedChild' => $selectedChild,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'plannedCount' => $plannedCount,
            'doneCount' => $doneCount,
            'skippedCount' => $skippedCount,
            'tooShortCount' => $tooShortCount,
            'minutesPlanned' => $minutesPlanned,
            'minutesCompleted' => $minutesCompleted,
            'completionPct' => min(100, $completionPct),
            'daysBreakdown' => $daysBreakdown,
            'subjectBreakdown' => $subjectBreakdown,
            'encouragements' => $encouragements,
        ]);
    }

    public function sendEncouragement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'message' => ['required', 'string', 'max:280'],
        ]);

        $child = User::findOrFail($validated['student_id']);
        abort_unless($request->user()->can('sendEncouragement', $child), 403, 'Unauthorized to send encouragement to this learner.');

        ParentEncouragement::create([
            'parent_id' => $request->user()->id,
            'student_id' => $child->id,
            'message' => trim($validated['message']),
        ]);

        return redirect()->route('parent.planner', ['child_id' => $child->id])
            ->with('status', "Encouragement sent to {$child->name}!");
    }

    public function deleteEncouragement(Request $request, int $encouragement): RedirectResponse
    {
        $enc = ParentEncouragement::findOrFail($encouragement);
        abort_unless($enc->parent_id === $request->user()->id, 403, 'You can only delete your own encouragement.');

        $childId = $enc->student_id;
        $enc->delete();

        return redirect()->route('parent.planner', ['child_id' => $childId])
            ->with('status', 'Encouragement removed.');
    }

    private function calculateStreak(int $studentId, Carbon $today): int
    {
        $streak = 0;
        $checkDate = $today->copy();

        // If today has no completed session, check if yesterday had one to maintain streak
        $todayHasSession = StudySession::where('user_id', $studentId)
            ->whereDate('planned_date', $checkDate)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true)
            ->exists();

        if (! $todayHasSession) {
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
