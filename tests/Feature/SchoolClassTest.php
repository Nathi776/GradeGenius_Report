<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_a_class_and_is_its_owner(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->post('/teacher/classes', [
            'name' => 'Grade 12 Maths A',
            'subject' => 'Mathematics',
            'grade' => 12,
            'target' => 70,
        ]);

        $schoolClass = SchoolClass::firstOrFail();
        $response->assertRedirect(route('teacher.classes.show', $schoolClass));
        $this->assertSame($teacher->id, $schoolClass->teacher_id);
    }

    public function test_class_creation_validates_input(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->post('/teacher/classes', ['name' => '', 'subject' => 'Maths', 'grade' => 40, 'target' => 150])
            ->assertSessionHasErrors(['name', 'grade', 'target']);
    }

    public function test_teacher_can_add_and_remove_a_learner_and_then_message_their_parent(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)->post("/teacher/classes/{$schoolClass->id}/learners", ['email' => $student->email])
            ->assertSessionHasNoErrors();
        $this->assertTrue($teacher->teachesStudent($student));

        $this->actingAs($teacher)->delete("/teacher/classes/{$schoolClass->id}/learners/{$student->id}");
        $this->assertFalse($teacher->fresh()->teachesStudent($student));
    }

    public function test_only_student_accounts_can_be_added_and_duplicates_are_rejected(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student = User::factory()->create(['role' => 'student']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id]);
        $schoolClass->learners()->attach($student->id);

        $this->actingAs($teacher)->post("/teacher/classes/{$schoolClass->id}/learners", ['email' => $parent->email])
            ->assertSessionHasErrors('email');
        $this->actingAs($teacher)->post("/teacher/classes/{$schoolClass->id}/learners", ['email' => $student->email])
            ->assertSessionHasErrors('email');
        $this->assertSame(1, $schoolClass->learners()->count());
    }

    public function test_teacher_cannot_view_or_change_another_teachers_class(): void
    {
        $owner = User::factory()->create(['role' => 'teacher']);
        $other = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $owner->id]);

        $this->actingAs($other)->get("/teacher/classes/{$schoolClass->id}")->assertNotFound();
        $this->actingAs($other)->post("/teacher/classes/{$schoolClass->id}/learners", ['email' => $student->email])->assertNotFound();
        $this->assertSame(0, $schoolClass->learners()->count());
    }

    public function test_classes_index_and_show_pages_render_for_the_owner(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id, 'name' => 'Grade 12 Maths A']);

        $this->actingAs($teacher)->get('/teacher/classes')->assertOk()->assertSee('Grade 12 Maths A');
        $this->actingAs($teacher)->get("/teacher/classes/{$schoolClass->id}")->assertOk()->assertSee('Add a learner');
        $this->actingAs($teacher)->get('/teacher/classes/create')->assertOk();
    }
}
