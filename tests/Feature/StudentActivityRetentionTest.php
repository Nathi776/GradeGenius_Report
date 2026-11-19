<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\StudentActivityTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentActivityRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sessions_older_than_ninety_days_and_their_events_are_pruned(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $oldSessionId = $this->createSession($student, str_repeat('a', 64), now()->subDays(91));
        $recentSessionId = $this->createSession($student, str_repeat('b', 64), now()->subDays(89));
        DB::table('student_activity_events')->insert([
            'student_activity_session_id' => $oldSessionId,
            'event_type' => 'Student dashboard',
            'occurred_at' => now()->subDays(91),
            'created_at' => now()->subDays(91),
            'updated_at' => now()->subDays(91),
        ]);

        $deleted = app(StudentActivityTracker::class)->pruneExpired();

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('student_activity_sessions', ['id' => $oldSessionId]);
        $this->assertDatabaseMissing('student_activity_events', ['student_activity_session_id' => $oldSessionId]);
        $this->assertDatabaseHas('student_activity_sessions', ['id' => $recentSessionId]);
    }

    private function createSession(User $student, string $key, Carbon $loginAt): int
    {
        return DB::table('student_activity_sessions')->insertGetId([
            'student_id' => $student->id,
            'session_key' => $key,
            'login_at' => $loginAt,
            'last_activity_at' => $loginAt,
            'logout_at' => $loginAt->copy()->addMinutes(10),
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'created_at' => $loginAt,
            'updated_at' => $loginAt,
        ]);
    }
}
