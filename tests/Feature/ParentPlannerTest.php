<?php

namespace Tests\Feature;

use App\Models\ParentEncouragement;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPlannerTest extends TestCase
{
    use RefreshDatabase;

    private function parent(): User
    {
        return User::factory()->create(['role' => 'parent']);
    }

    private function student(string $name = 'Thokozani M'): User
    {
        return User::factory()->create(['role' => 'student', 'name' => $name]);
    }

    private function linkParentAndStudent(User $parent, User $student, string $status = 'verified'): void
    {
        $parent->students()->attach($student->id, [
            'linked_at' => now(),
            'status' => $status,
        ]);
    }

    public function test_parent_can_view_verified_child_planner_read_only(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = $this->student();
        $this->linkParentAndStudent($parent, $student, 'verified');

        StudySession::factory()->create([
            'user_id' => $student->id,
            'topic' => 'Algebra Foundations',
            'planned_date' => now()->toDateString(),
            'minutes' => 30,
        ]);

        $response = $this->actingAs($parent)->get('/parent/planner?child_id='.$student->id);

        $response->assertOk();
        $response->assertSee("Thokozani M's Revision Plan", false);
        $response->assertSee('Algebra Foundations');
        $response->assertSee('Read-only view');
        // Ensure no student edit/action buttons exist
        $response->assertDontSee('Rebuild my plan');
        $response->assertDontSee('Build my plan');
        $response->assertDontSee('+ Add Session');
        $response->assertDontSee('Start Session');
    }

    public function test_parent_cannot_view_unlinked_child_planner_returns_403(): void
    {
        $parent = $this->parent();
        $otherStudent = $this->student('Unlinked Student');

        $this->actingAs($parent)->get('/parent/planner?child_id='.$otherStudent->id)
            ->assertForbidden();
    }

    public function test_unverified_parent_link_returns_403(): void
    {
        $parent = $this->parent();
        $student = $this->student();
        $this->linkParentAndStudent($parent, $student, 'pending');

        $this->actingAs($parent)->get('/parent/planner?child_id='.$student->id)
            ->assertForbidden();
    }

    public function test_parent_cannot_call_student_done_endpoint(): void
    {
        $parent = $this->parent();
        $student = $this->student();
        $this->linkParentAndStudent($parent, $student, 'verified');

        $session = StudySession::factory()->create([
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(30),
        ]);

        $this->actingAs($parent)->patch("/student/planner/{$session->id}", ['status' => 'done'])
            ->assertForbidden();
    }

    public function test_fake_2_minute_session_displays_as_not_counted_on_parent_view(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = $this->student();
        $this->linkParentAndStudent($parent, $student, 'verified');

        StudySession::factory()->tooShort()->create([
            'user_id' => $student->id,
            'topic' => 'Probability',
            'planned_date' => now()->toDateString(),
            'actual_minutes' => 2,
        ]);

        $this->actingAs($parent)->get('/parent/planner?child_id='.$student->id)
            ->assertOk()
            ->assertSee('Marked Done in 2 min - Not counted');
    }

    public function test_parent_can_send_word_of_encouragement(): void
    {
        $parent = $this->parent();
        $student = $this->student();
        $this->linkParentAndStudent($parent, $student, 'verified');

        $response = $this->actingAs($parent)->post('/parent/encouragements', [
            'student_id' => $student->id,
            'message' => 'Keep pushing, you are almost there!',
        ]);

        $response->assertRedirect('/parent/planner?child_id='.$student->id);
        $this->assertDatabaseHas('parent_encouragements', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'message' => 'Keep pushing, you are almost there!',
        ]);
    }

    public function test_encouragement_for_child_a_does_not_show_to_child_b(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $childA = $this->student('Child A');
        $childB = $this->student('Child B');
        $this->linkParentAndStudent($parent, $childA, 'verified');
        $this->linkParentAndStudent($parent, $childB, 'verified');

        ParentEncouragement::create([
            'parent_id' => $parent->id,
            'student_id' => $childA->id,
            'message' => 'Special message for Child A only',
        ]);

        // Child A sees it
        $this->actingAs($childA)->get('/student/planner')
            ->assertOk()
            ->assertSee('Special message for Child A only');

        // Child B does not see it
        $this->actingAs($childB)->get('/student/planner')
            ->assertOk()
            ->assertDontSee('Special message for Child A only');
    }

    public function test_parent_can_delete_only_their_own_encouragement(): void
    {
        $parentA = $this->parent();
        $parentB = User::factory()->create(['role' => 'parent']);
        $student = $this->student();
        $this->linkParentAndStudent($parentA, $student, 'verified');
        $this->linkParentAndStudent($parentB, $student, 'verified');

        $enc = ParentEncouragement::create([
            'parent_id' => $parentA->id,
            'student_id' => $student->id,
            'message' => 'Message from Parent A',
        ]);

        // Parent B cannot delete Parent A's encouragement
        $this->actingAs($parentB)->delete("/parent/encouragements/{$enc->id}")
            ->assertForbidden();

        // Parent A can delete
        $this->actingAs($parentA)->delete("/parent/encouragements/{$enc->id}")
            ->assertRedirect('/parent/planner?child_id='.$student->id);

        $this->assertDatabaseMissing('parent_encouragements', ['id' => $enc->id]);
    }

    public function test_parent_can_view_weekly_recap(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = $this->student();
        $this->linkParentAndStudent($parent, $student, 'verified');

        StudySession::factory()->done()->create([
            'user_id' => $student->id,
            'topic' => 'Calculus Basics',
            'planned_date' => now()->toDateString(),
            'actual_minutes' => 30,
        ]);

        $this->actingAs($parent)->get('/parent/planner/recap?child_id='.$student->id)
            ->assertOk()
            ->assertSee('Weekly Study Performance Recap')
            ->assertSee('Thokozani M')
            ->assertSee('Calculus Basics')
            ->assertSee('Daily breakdown')
            ->assertSee('Subject breakdown');
    }
}
