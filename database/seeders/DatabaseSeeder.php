<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('DemoPass123!');

        $student = User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Demo Student', 'password' => $password, 'role' => 'student'],
        );
        $parent = User::updateOrCreate(
            ['email' => 'parent@example.com'],
            ['name' => 'Demo Parent', 'password' => $password, 'role' => 'parent'],
        );
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'Demo Teacher', 'password' => $password, 'role' => 'teacher'],
        );

        foreach ([$student, $parent, $teacher] as $user) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $parent->students()->syncWithoutDetaching([
            $student->id => ['linked_at' => now()],
        ]);

        foreach ([1, 3, 5] as $daysAgo) {
            $loginAt = now()->subDays($daysAgo)->setTime(15, 30);
            $logoutAt = $loginAt->copy()->addMinutes(24);
            $sessionKey = hash('sha256', "demo-session-{$student->id}-{$daysAgo}");

            DB::table('student_activity_sessions')->updateOrInsert(
                ['session_key' => $sessionKey],
                [
                    'student_id' => $student->id,
                    'login_at' => $loginAt,
                    'last_activity_at' => $logoutAt,
                    'logout_at' => $logoutAt,
                    'browser' => 'Chrome',
                    'platform' => 'Windows',
                    'created_at' => $loginAt,
                    'updated_at' => $logoutAt,
                ],
            );

            $sessionId = DB::table('student_activity_sessions')
                ->where('session_key', $sessionKey)
                ->value('id');
            DB::table('student_activity_events')
                ->where('student_activity_session_id', $sessionId)
                ->delete();
            DB::table('student_activity_events')->insert([
                [
                    'student_activity_session_id' => $sessionId,
                    'event_type' => 'Student dashboard',
                    'occurred_at' => $loginAt,
                    'created_at' => $loginAt,
                    'updated_at' => $loginAt,
                ],
                [
                    'student_activity_session_id' => $sessionId,
                    'event_type' => 'Academic report',
                    'occurred_at' => $logoutAt,
                    'created_at' => $logoutAt,
                    'updated_at' => $logoutAt,
                ],
            ]);
        }
    }
}
