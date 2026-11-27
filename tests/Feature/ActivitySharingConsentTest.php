<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ParentTelemetryService;
use App\Services\TeacherLearnerPresenceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivitySharingConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_is_private_by_default(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->assertFalse($student->fresh()->shares_activity);
    }

    public function test_student_can_turn_sharing_on_and_off(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post('/student/privacy/activity-sharing', ['shares_activity' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($student->fresh()->shares_activity);

        $this->actingAs($student)->post('/student/privacy/activity-sharing', ['shares_activity' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($student->fresh()->shares_activity);
    }

    public function test_only_students_can_change_the_setting(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)->post('/student/privacy/activity-sharing', ['shares_activity' => 1])->assertForbidden();
    }

    public function test_parent_sees_no_activity_for_a_learner_who_keeps_it_private(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $student = User::factory()->create(['role' => 'student']);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => 'verified']);
        $this->actingAs($student)->get('/student')->assertOk();
        $this->assertSame(1, DB::table('student_activity_sessions')->where('student_id', $student->id)->count());

        $private = app(ParentTelemetryService::class)->forParent($parent)[0];
        $this->assertFalse($private['sharing']);
        $this->assertSame('Not shared', $private['status_label']);
        $this->assertSame([], $private['sessions']);
        $this->assertSame(0, $private['logins_7d']);

        $student->forceFill(['shares_activity' => true])->save();
        $shared = app(ParentTelemetryService::class)->forParent($parent)[0];
        $this->assertTrue($shared['sharing']);
        $this->assertSame(1, $shared['logins_7d']);
    }

    public function test_teacher_presence_is_hidden_for_a_learner_who_keeps_it_private(): void
    {
        config(['session.driver' => 'database', 'session.table' => 'sessions']);
        $student = User::factory()->create(['role' => 'student']);
        DB::table('sessions')->insert([
            'id' => 'recent', 'user_id' => $student->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $presence = app(TeacherLearnerPresenceService::class)->forUserIds([$student->id]);
        $this->assertSame('unavailable', $presence[$student->id]['state']);
        $this->assertSame('Not shared', $presence[$student->id]['label']);

        $student->forceFill(['shares_activity' => true])->save();
        $this->assertSame('online', app(TeacherLearnerPresenceService::class)->forUserIds([$student->id])[$student->id]['state']);
    }

    public function test_student_settings_shows_the_sharing_switch_and_the_dashboard_shows_the_state(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('student.settings'))
            ->assertOk()
            ->assertSee('Private, only you can see it')
            ->assertSee('Share my activity with parents and teachers');

        $this->actingAs($student)->get('/student')
            ->assertOk()
            ->assertSee('Activity sharing:')
            ->assertSee('Manage in Settings');
    }

    public function test_demo_seeders_do_not_run_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, User::count());
    }
}
