<?php

namespace Tests\Feature;

use App\Models\ParentEncouragement;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPlannerTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_empty_planner_invites_the_student_to_build_a_plan(): void
    {
        $this->withoutVite();

        $this->actingAs($this->student())->get('/student/planner')
            ->assertOk()
            ->assertSee('Study planner')
            ->assertSee('Build my plan')
            ->assertSee('You have no sessions planned yet');
    }

    public function test_building_a_plan_saves_sessions_for_the_signed_in_student_only(): void
    {
        $this->withoutVite();
        $student = $this->student();
        $other = $this->student();

        $this->actingAs($student)->post('/student/planner')->assertRedirect('/student/planner');

        $this->assertGreaterThan(0, $student->studySessions()->count());
        $this->assertSame(0, $other->studySessions()->count());

        $this->actingAs($student)->get('/student/planner')
            ->assertOk()
            ->assertSee('Probability')
            ->assertSee('Rebuild my plan');
    }

    public function test_rebuilding_keeps_done_sessions_and_replaces_planned_ones(): void
    {
        $student = $this->student();
        $done = StudySession::factory()->done()->create(['user_id' => $student->id, 'topic' => 'Old topic']);
        $stale = StudySession::factory()->create(['user_id' => $student->id, 'topic' => 'Stale topic']);

        $this->actingAs($student)->post('/student/planner');

        $this->assertDatabaseHas('study_sessions', ['id' => $done->id]);
        $this->assertDatabaseMissing('study_sessions', ['id' => $stale->id]);
    }

    public function test_a_session_can_be_marked_done_skipped_and_undone(): void
    {
        $student = $this->student();
        $session = StudySession::factory()->create([
            'user_id' => $student->id,
            'planned_date' => now()->toDateString(),
            'started_at' => now()->subMinutes(30),
            'minutes' => 30,
        ]);

        $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'done']);
        $this->assertSame('done', $session->fresh()->status);
        $this->assertTrue($session->fresh()->is_counted);
        $this->assertNotNull($session->fresh()->completed_at);

        $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'skipped']);
        $this->assertSame('skipped', $session->fresh()->status);
        $this->assertFalse($session->fresh()->is_counted);
        $this->assertNull($session->fresh()->completed_at);

        $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'planned']);
        $this->assertSame('planned', $session->fresh()->status);
    }

    public function test_two_minute_cheat_is_flagged_too_short_and_not_counted(): void
    {
        $student = $this->student();
        $session = StudySession::factory()->create([
            'user_id' => $student->id,
            'planned_date' => now()->toDateString(),
            'started_at' => now()->subMinutes(2),
            'minutes' => 30,
        ]);

        $response = $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'done']);

        $response->assertSessionHas('error');
        $fresh = $session->fresh();
        $this->assertSame('too_short', $fresh->status);
        $this->assertFalse($fresh->is_counted);
        $this->assertNull($fresh->completed_at);
    }

    public function test_future_sessions_cannot_be_completed_or_skipped_before_their_date(): void
    {
        $student = $this->student();
        $session = StudySession::factory()->create([
            'user_id' => $student->id,
            'planned_date' => now()->addDays(2)->toDateString(),
            'started_at' => now()->subMinutes(30),
            'minutes' => 30,
        ]);

        $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'done'])
            ->assertSessionHas('error');
        $this->assertSame('planned', $session->fresh()->status);

        $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'skipped'])
            ->assertSessionHas('error');
        $this->assertSame('planned', $session->fresh()->status);
    }

    public function test_child_sees_word_of_encouragement_from_parent(): void
    {
        $this->withoutVite();
        $student = $this->student();
        $parent = User::factory()->create(['role' => 'parent', 'name' => 'Sam Mthombeni']);
        ParentEncouragement::create([
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'message' => 'You got this, focus on Probability today!',
        ]);

        $this->actingAs($student)->get('/student/planner')
            ->assertOk()
            ->assertSee('Word of encouragement from Sam Mthombeni')
            ->assertSee('You got this, focus on Probability today!');
    }

    public function test_an_invalid_status_is_rejected(): void
    {
        $student = $this->student();
        $session = StudySession::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)->patch("/student/planner/{$session->id}", ['status' => 'banana'])
            ->assertSessionHasErrors('status');
    }

    public function test_a_student_cannot_change_or_remove_another_students_session(): void
    {
        $owner = $this->student();
        $session = StudySession::factory()->create(['user_id' => $owner->id]);
        $intruder = $this->student();

        $this->actingAs($intruder)->patch("/student/planner/{$session->id}", ['status' => 'done'])->assertNotFound();
        $this->actingAs($intruder)->delete("/student/planner/{$session->id}")->assertNotFound();

        $this->assertSame('planned', $session->fresh()->status);
    }

    public function test_a_session_can_be_removed(): void
    {
        $student = $this->student();
        $session = StudySession::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)->delete("/student/planner/{$session->id}")->assertRedirect('/student/planner');

        $this->assertDatabaseMissing('study_sessions', ['id' => $session->id]);
    }

    public function test_only_students_can_open_the_planner(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)->get('/student/planner')->assertForbidden();
        $this->get('/student/planner')->assertRedirect('/login');
    }
}
