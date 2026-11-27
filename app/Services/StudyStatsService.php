<?php

namespace App\Services;

use App\Models\StudySession;
use Illuminate\Support\Carbon;

/**
 * Verified study time, weekly total and streak for one learner, from completed counted planner sessions.
 *
 * Callers that show this to anyone other than the learner must check the learner's activity-sharing
 * setting first; this service does not.
 */
final class StudyStatsService
{
    public const WEEKLY_GOAL_MINUTES = 630;

    /**
     * @return array{total_minutes: int, weekly_minutes: int, weekly_goal: int, streak: int}
     */
    public function forStudent(int $studentId): array
    {
        $today = now()->startOfDay();
        $counted = fn () => StudySession::where('user_id', $studentId)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true);

        return [
            'total_minutes' => (int) $counted()->sum('actual_minutes'),
            'weekly_minutes' => (int) $counted()
                ->whereBetween('planned_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
                ->sum('actual_minutes'),
            'weekly_goal' => self::WEEKLY_GOAL_MINUTES,
            'streak' => $this->streak($studentId, $today),
        ];
    }

    private function streak(int $studentId, Carbon $today): int
    {
        $hasDoneOn = fn (Carbon $day): bool => StudySession::where('user_id', $studentId)
            ->whereDate('planned_date', $day)
            ->where('status', StudySession::STATUS_DONE)
            ->where('is_counted', true)
            ->exists();

        $day = $today->copy();
        if (! $hasDoneOn($day)) {
            $day->subDay();
        }

        $streak = 0;
        while ($hasDoneOn($day)) {
            $streak++;
            $day->subDay();
        }

        return $streak;
    }
}
