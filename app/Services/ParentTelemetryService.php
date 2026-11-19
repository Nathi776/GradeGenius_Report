<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class ParentTelemetryService
{
    private const ONLINE_WINDOW_MINUTES = 5;

    private const RETENTION_DAYS = 90;

    /**
     * Return only telemetry for student accounts linked to the signed-in parent.
     *
     * @return list<array<string, mixed>>
     */
    public function forParent(User $parent): array
    {
        $students = $parent->students()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        return $students->map(fn (User $student): array => $this->forStudent($student))->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function forStudent(User $student): array
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS);
        $weekStart = now()->subDays(7);
        $weeklySessions = DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->where('login_at', '>=', $weekStart)
            ->get(['login_at', 'last_activity_at', 'logout_at']);
        $observedSeconds = $weeklySessions->sum(function (object $session): int {
            $start = Carbon::parse($session->login_at);
            $end = Carbon::parse($session->logout_at ?? $session->last_activity_at);

            return max(0, (int) $start->diffInSeconds($end));
        });
        $durationHours = intdiv($observedSeconds, 3600);
        $durationMinutes = intdiv($observedSeconds % 3600, 60);
        $online = DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->whereNull('logout_at')
            ->where('last_activity_at', '>=', now()->subMinutes(self::ONLINE_WINDOW_MINUTES))
            ->exists();
        $sessions = DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->where('last_activity_at', '>=', $cutoff)
            ->orderByDesc('login_at')
            ->limit(20)
            ->get();

        $sessionIds = $sessions->pluck('id')->all();
        $eventsBySession = $sessionIds === []
            ? collect()
            : DB::table('student_activity_events')
                ->whereIn('student_activity_session_id', $sessionIds)
                ->orderBy('occurred_at')
                ->get()
                ->groupBy('student_activity_session_id');

        $now = now();
        $latest = DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->where('last_activity_at', '>=', $cutoff)
            ->orderByDesc('last_activity_at')
            ->first();
        $recordedVisits = DB::table('student_activity_events')
            ->join('student_activity_sessions', 'student_activity_sessions.id', '=', 'student_activity_events.student_activity_session_id')
            ->where('student_activity_sessions.student_id', $student->id)
            ->where('student_activity_events.occurred_at', '>=', $cutoff)
            ->count();
        $recent = $sessions->map(function (object $session) use ($eventsBySession, $now): array {
            $lastSeen = Carbon::parse($session->last_activity_at);
            $end = Carbon::parse($session->logout_at ?? $session->last_activity_at);
            $login = Carbon::parse($session->login_at);
            $minutes = max(0, $login->diffInMinutes($end));
            $events = $eventsBySession->get($session->id, collect());

            return [
                'date_label' => $login->isToday() ? 'Today · '.$login->format('j M Y')
                    : ($login->isYesterday() ? 'Yesterday · '.$login->format('j M Y') : $login->format('D · j M Y')),
                'login_label' => $login->format('H:i'),
                'last_seen_label' => $lastSeen->format('H:i'),
                'end_label' => $session->logout_at === null
                    ? 'Last request '.$lastSeen->format('H:i')
                    : 'Logged out '.Carbon::parse($session->logout_at)->format('H:i'),
                'duration_label' => $minutes < 60 ? "{$minutes} min" : intdiv($minutes, 60).'h '.($minutes % 60).'m',
                'status_label' => $session->logout_at !== null
                    ? 'Logged out'
                    : ($lastSeen->greaterThanOrEqualTo($now->copy()->subMinutes(self::ONLINE_WINDOW_MINUTES)) ? 'Online' : 'Offline'),
                'browser' => $session->browser,
                'platform' => $session->platform,
                'events' => $events->map(fn (object $event): array => [
                    'time' => Carbon::parse($event->occurred_at)->format('H:i'),
                    'label' => $event->event_type,
                ])->values()->all(),
            ];
        })->all();

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'logins_7d' => $weeklySessions->count(),
            'duration_7d' => "{$durationHours}h {$durationMinutes}m",
            'status_label' => $online ? 'Online' : ($latest === null ? 'No activity' : 'Offline'),
            'last_active' => $latest === null ? null : Carbon::parse($latest->last_activity_at)->diffForHumans(),
            'last_active_label' => $latest === null ? null : Carbon::parse($latest->last_activity_at)->format('D · j M Y, H:i'),
            'latest_browser' => $latest->browser ?? null,
            'latest_platform' => $latest->platform ?? null,
            'recorded_visits' => $recordedVisits,
            'sessions' => $recent,
        ];
    }
}
