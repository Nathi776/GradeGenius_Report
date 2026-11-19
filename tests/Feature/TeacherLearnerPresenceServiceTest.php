<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TeacherLearnerPresenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherLearnerPresenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_database_sessions_are_online_and_stale_or_missing_sessions_are_offline(): void
    {
        config(['session.driver' => 'database', 'session.table' => 'sessions']);

        $online = User::factory()->create(['role' => 'student']);
        $offline = User::factory()->create(['role' => 'student']);
        $neverSeen = User::factory()->create(['role' => 'student']);
        $notStudent = User::factory()->create(['role' => 'parent']);

        DB::table('sessions')->insert([
            [
                'id' => 'recent-session',
                'user_id' => $online->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => '',
                'last_activity' => now()->subMinutes(4)->timestamp,
            ],
            [
                'id' => 'stale-session',
                'user_id' => $offline->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => '',
                'last_activity' => now()->subMinutes(6)->timestamp,
            ],
        ]);

        $presence = app(TeacherLearnerPresenceService::class)
            ->forUserIds([$online->id, $offline->id, $neverSeen->id, $notStudent->id]);

        $this->assertSame('online', $presence[$online->id]['state']);
        $this->assertSame('offline', $presence[$offline->id]['state']);
        $this->assertSame('offline', $presence[$neverSeen->id]['state']);
        $this->assertSame('unavailable', $presence[$notStudent->id]['state']);
        $this->assertNotNull($presence[$offline->id]['last_active']);
        $this->assertNull($presence[$neverSeen->id]['last_active']);
    }

    public function test_presence_is_unavailable_when_sessions_are_not_database_backed(): void
    {
        config(['session.driver' => 'file']);

        $presence = app(TeacherLearnerPresenceService::class)->forUserIds([42]);

        $this->assertSame('unavailable', $presence[42]['state']);
    }
}
