<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class StudentActivityTracker
{
    private const RETENTION_DAYS = 90;

    private const TRACKED_ROUTES = [
        'student.home' => 'Student dashboard',
        'student.report' => 'Academic report',
    ];

    public function start(Request $request, User $student): void
    {
        if ($student->role !== 'student' || ! $student->hasVerifiedEmail()) {
            return;
        }

        $sessionKey = $this->sessionKey($request);
        $now = now();
        $sessionId = $request->session()->get('student_activity_session_id');
        $session = is_numeric($sessionId)
            ? DB::table('student_activity_sessions')
                ->where('id', (int) $sessionId)
                ->where('student_id', $student->id)
                ->whereNull('logout_at')
                ->first()
            : null;
        $session ??= DB::table('student_activity_sessions')
            ->where('session_key', $sessionKey)
            ->where('student_id', $student->id)
            ->whereNull('logout_at')
            ->first();

        if ($session === null) {
            $sessionId = DB::table('student_activity_sessions')->insertGetId([
                'student_id' => $student->id,
                'session_key' => $sessionKey,
                'login_at' => $now,
                'last_activity_at' => $now,
                'logout_at' => null,
                'browser' => $this->browser($request->userAgent() ?? ''),
                'platform' => $this->platform($request->userAgent() ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $sessionId = $session->id;
            DB::table('student_activity_sessions')
                ->where('id', $sessionId)
                ->update([
                    'session_key' => $sessionKey,
                    'last_activity_at' => $now,
                    'browser' => $this->browser($request->userAgent() ?? ''),
                    'platform' => $this->platform($request->userAgent() ?? ''),
                    'updated_at' => $now,
                ]);
        }
        $request->session()->put('student_activity_session_id', $sessionId);

        $this->pruneExpired();
    }

    /**
     * @return array{current_session: array{started_at: int, elapsed: string, browser: string, platform: string}|null, sessions_this_week: int}
     */
    public function dashboardSnapshot(Request $request, User $student): array
    {
        $this->start($request, $student);
        $sessionId = $request->session()->get('student_activity_session_id');
        $session = is_numeric($sessionId)
            ? DB::table('student_activity_sessions')
                ->where('id', (int) $sessionId)
                ->where('student_id', $student->id)
                ->whereNull('logout_at')
                ->first()
            : null;

        $weekStart = now()->subDays(7);
        $sessionsThisWeek = DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->where('login_at', '>=', $weekStart)
            ->count();

        if ($session === null) {
            return ['current_session' => null, 'sessions_this_week' => $sessionsThisWeek];
        }

        $startedAt = Carbon::parse($session->login_at);
        $elapsedSeconds = max(0, now()->timestamp - $startedAt->timestamp);
        $hours = intdiv($elapsedSeconds, 3600);

        return [
            'current_session' => [
                'started_at' => $startedAt->timestamp,
                'elapsed' => sprintf('%02d:%02d:%02d', $hours, intdiv($elapsedSeconds % 3600, 60), $elapsedSeconds % 60),
                'browser' => $session->browser,
                'platform' => $session->platform,
            ],
            'sessions_this_week' => $sessionsThisWeek,
        ];
    }

    public function recordPageVisit(Request $request, User $student): void
    {
        if ($student->role !== 'student' || ! $student->hasVerifiedEmail()) {
            return;
        }

        $routeName = $request->route()?->getName();
        $eventType = is_string($routeName) ? (self::TRACKED_ROUTES[$routeName] ?? null) : null;
        if ($eventType === null) {
            return;
        }

        $sessionId = $request->session()->get('student_activity_session_id');
        $session = is_numeric($sessionId)
            ? DB::table('student_activity_sessions')
                ->where('id', (int) $sessionId)
                ->where('student_id', $student->id)
                ->first()
            : null;

        if ($session === null) {
            $this->start($request, $student);
            $session = DB::table('student_activity_sessions')
                ->where('session_key', $this->sessionKey($request))
                ->where('student_id', $student->id)
                ->first();
        }

        $now = now();
        DB::table('student_activity_sessions')
            ->where('id', $session->id)
            ->update([
                'session_key' => $this->sessionKey($request),
                'last_activity_at' => $now,
                'updated_at' => $now,
            ]);
        $request->session()->put('student_activity_session_id', $session->id);
        DB::table('student_activity_events')->insert([
            'student_activity_session_id' => $session->id,
            'event_type' => $eventType,
            'occurred_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function end(Request $request, User $student): void
    {
        if ($student->role !== 'student') {
            return;
        }

        $now = now();
        $sessionId = $request->session()->get('student_activity_session_id');
        DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->where(function ($query) use ($sessionId, $request): void {
                if (is_numeric($sessionId)) {
                    $query->where('id', (int) $sessionId);
                } else {
                    $query->where('session_key', $this->sessionKey($request));
                }
            })
            ->whereNull('logout_at')
            ->update([
                'last_activity_at' => $now,
                'logout_at' => $now,
                'updated_at' => $now,
            ]);
    }

    public function pruneExpired(): int
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS);

        return DB::table('student_activity_sessions')
            ->where('login_at', '<', $cutoff)
            ->delete();
    }

    private function sessionKey(Request $request): string
    {
        return hash('sha256', $request->session()->getId());
    }

    private function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') && ! str_contains($agent, 'Edg/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Other browser',
        };
    }

    private function platform(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Macintosh'), str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Unknown device',
        };
    }
}
