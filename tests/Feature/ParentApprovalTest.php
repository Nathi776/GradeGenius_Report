<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function parent(string $email = 'guardian@example.com'): User
    {
        return User::factory()->create([
            'role' => 'parent',
            'email' => $email,
            'email_verified_at' => now(),
        ]);
    }

    private function student(string $name = 'Thabo M'): User
    {
        return User::factory()->create([
            'role' => 'student',
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    public function test_student_registration_with_parent_email_creates_pending_link(): void
    {
        $parent = $this->parent('parent@school.co.za');

        $this->post('/register', [
            'name' => 'Kabelo Student',
            'email' => 'kabelo@school.co.za',
            'role' => 'student',
            'parent_email' => $parent->email,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect('/student');

        $student = User::where('email', 'kabelo@school.co.za')->firstOrFail();

        // Check link exists and is pending
        $this->assertDatabaseHas('parent_student_links', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);

        // Student should not be in linkedChildren (verified only)
        $this->assertFalse($parent->linkedChildren()->where('users.id', $student->id)->exists());

        // Student should be in pendingChildren
        $this->assertTrue($parent->pendingChildren()->where('users.id', $student->id)->exists());
    }

    public function test_student_guardian_link_from_dashboard_is_pending_until_approved(): void
    {
        $parent = $this->parent('parent2@school.co.za');
        $student = $this->student('Zanele S');

        $this->actingAs($student)->post('/student/guardians', [
            'parent_email' => $parent->email,
        ])->assertRedirect();

        $this->assertDatabaseHas('parent_student_links', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_parent_sees_pending_link_request_banner_on_dashboard(): void
    {
        $parent = $this->parent();
        $student = $this->student('Pending Child');
        $parent->students()->attach($student->id, ['status' => 'pending', 'linked_at' => now()]);

        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('Pending Learner Link Requests')
            ->assertSee('Pending Child')
            ->assertSee($student->email)
            ->assertSee('Confirm Link')
            ->assertSee('Decline');
    }

    public function test_parent_can_confirm_pending_link_request(): void
    {
        $parent = $this->parent();
        $student = $this->student('Sipho Learner');
        $parent->students()->attach($student->id, ['status' => 'pending', 'linked_at' => now()]);

        $this->actingAs($parent)
            ->post("/parent/links/{$student->id}/confirm")
            ->assertRedirect();

        $this->assertDatabaseHas('parent_student_links', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'status' => 'verified',
        ]);

        $this->assertTrue($parent->linkedChildren()->where('users.id', $student->id)->exists());
        $this->assertFalse($parent->pendingChildren()->where('users.id', $student->id)->exists());
    }

    public function test_parent_can_reject_pending_link_request(): void
    {
        $parent = $this->parent();
        $student = $this->student('Unknown Student');
        $parent->students()->attach($student->id, ['status' => 'pending', 'linked_at' => now()]);

        $this->actingAs($parent)
            ->post("/parent/links/{$student->id}/reject")
            ->assertRedirect();

        $this->assertDatabaseMissing('parent_student_links', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_pending_parent_link_cannot_view_child_planner(): void
    {
        $parent = $this->parent();
        $student = $this->student('Pending Only');
        $parent->students()->attach($student->id, ['status' => 'pending', 'linked_at' => now()]);

        $this->actingAs($parent)
            ->get("/parent/planner?child_id={$student->id}")
            ->assertForbidden();
    }
}
