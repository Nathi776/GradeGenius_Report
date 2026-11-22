<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\TeacherMessage;
use App\Models\User;
use App\Notifications\TeacherMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherCommunicationTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(string $name = 'Mrs Naidoo'): User
    {
        return User::factory()->create([
            'role' => 'teacher',
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    private function parent(string $name = 'Mr Khumalo'): User
    {
        return User::factory()->create([
            'role' => 'parent',
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    private function student(string $name = 'Nathi K'): User
    {
        return User::factory()->create([
            'role' => 'student',
            'name' => $name,
            'email_verified_at' => now(),
        ]);
    }

    public function test_teacher_class_relationship_and_teaches_student_check(): void
    {
        $teacher = $this->teacher();
        $student = $this->student();
        $otherStudent = $this->student('Unenrolled Student');

        $class = SchoolClass::create([
            'teacher_id' => $teacher->id,
            'name' => 'Grade 12A',
            'subject' => 'Mathematics',
            'grade' => 12,
            'target' => 70,
        ]);

        $class->learners()->attach($student->id);

        $this->assertTrue($teacher->teachesStudent($student));
        $this->assertFalse($teacher->teachesStudent($otherStudent));
    }

    public function test_teacher_cannot_message_student_they_do_not_teach(): void
    {
        $teacher = $this->teacher();
        $student = $this->student();
        $parent = $this->parent();
        $parent->students()->attach($student->id, ['status' => 'verified', 'linked_at' => now()]);

        // Teacher does not teach student
        $this->actingAs($teacher)
            ->get("/teacher/messages/create?student_id={$student->id}")
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post('/teacher/messages', [
                'student_id' => $student->id,
                'parent_id' => $parent->id,
                'type' => 'concern',
                'subject' => 'Test Subject',
                'body' => 'This is a test message regarding performance.',
            ])
            ->assertForbidden();
    }

    public function test_teacher_cannot_message_unverified_parent(): void
    {
        $teacher = $this->teacher();
        $student = $this->student();
        $class = SchoolClass::create([
            'teacher_id' => $teacher->id,
            'name' => 'Grade 12A',
            'subject' => 'Mathematics',
            'grade' => 12,
        ]);
        $class->learners()->attach($student->id);

        $parent = $this->parent();
        // Link is only pending, not verified!
        $parent->students()->attach($student->id, ['status' => 'pending', 'linked_at' => now()]);

        $this->actingAs($teacher)
            ->get("/teacher/messages/create?student_id={$student->id}")
            ->assertRedirect('/teacher')
            ->assertSessionHas('error');

        $this->actingAs($teacher)
            ->post('/teacher/messages', [
                'student_id' => $student->id,
                'parent_id' => $parent->id,
                'type' => 'concern',
                'subject' => 'Test Subject',
                'body' => 'This is a test message regarding performance.',
            ])
            ->assertForbidden();
    }

    public function test_teacher_can_send_note_to_verified_parent_with_in_app_notification(): void
    {
        $teacher = $this->teacher();
        $student = $this->student('Nathi');
        $parent = $this->parent('Mr Khumalo');

        $class = SchoolClass::create([
            'teacher_id' => $teacher->id,
            'name' => 'Grade 12A',
            'subject' => 'Mathematics',
            'grade' => 12,
            'target' => 65,
        ]);
        $class->learners()->attach($student->id);

        $parent->students()->attach($student->id, ['status' => 'verified', 'linked_at' => now()]);

        // Open create form
        $this->actingAs($teacher)
            ->get("/teacher/messages/create?student_id={$student->id}&subject=Mathematics&mark=51&target=65")
            ->assertOk()
            ->assertSee('Compose Educator Note')
            ->assertSee('Nathi')
            ->assertSee($parent->name);

        // Submit message
        $response = $this->actingAs($teacher)
            ->post('/teacher/messages', [
                'student_id' => $student->id,
                'parent_id' => $parent->id,
                'type' => 'concern',
                'subject' => 'Academic Concern: Nathi (Mathematics)',
                'body' => "Nathi's Mathematics mark is 51%, below the 65% target. Probability and Geometry are the weakest topics.",
            ]);

        $response->assertRedirect('/teacher/messages');

        // Verify message created
        $this->assertDatabaseHas('teacher_messages', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => 'concern',
            'subject' => 'Academic Concern: Nathi (Mathematics)',
        ]);

        $message = TeacherMessage::firstOrFail();
        $this->assertNull($message->read_at);

        // Verify parent received database notification
        $this->assertCount(1, $parent->unreadNotifications);
        $notification = $parent->unreadNotifications->first();
        $this->assertSame(TeacherMessageNotification::class, $notification->type);
        $this->assertSame($message->id, $notification->data['message_id']);
    }

    public function test_parent_views_message_marks_read_and_can_acknowledge(): void
    {
        $teacher = $this->teacher();
        $student = $this->student('Nathi');
        $parent = $this->parent('Mr Khumalo');

        $message = TeacherMessage::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => 'concern',
            'subject' => 'Academic Notice',
            'body' => "Please review Nathi's recent past paper score.",
        ]);

        $parent->notify(new TeacherMessageNotification($message));
        $this->assertSame(1, $parent->unreadNotifications()->count());

        // Parent views message
        $this->actingAs($parent)
            ->get("/parent/messages/{$message->id}")
            ->assertOk()
            ->assertSee('Academic Notice')
            ->assertSee("Please review Nathi's recent past paper score.")
            ->assertSee('Acknowledge this note')
            ->assertSee('Thanks, noted');

        $this->assertNotNull($message->fresh()->read_at);
        $this->assertSame(0, $parent->unreadNotifications()->count());

        // Parent acknowledges with quick reply
        $this->actingAs($parent)
            ->post("/parent/messages/{$message->id}/acknowledge", [
                'quick_reply' => 'Thanks, noted',
            ])
            ->assertRedirect();

        $fresh = $message->fresh();
        $this->assertNotNull($fresh->acknowledged_at);
        $this->assertSame('Thanks, noted', $fresh->quick_reply);

        // Teacher views sent messages outbox and sees read and acknowledged status
        $this->actingAs($teacher)
            ->get('/teacher/messages')
            ->assertOk()
            ->assertSee('Academic Notice')
            ->assertSee('Thanks, noted');
    }

    public function test_notification_center_and_mark_all_read(): void
    {
        $parent = $this->parent();
        $teacher = $this->teacher();
        $student = $this->student();

        $message = TeacherMessage::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => 'praise',
            'subject' => 'Great work in Physics',
            'body' => 'Keep up the great effort!',
        ]);

        $parent->notify(new TeacherMessageNotification($message));

        // Parent visits notifications center
        $this->actingAs($parent)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('Great work in Physics')
            ->assertSee('1 Unread');

        // Mark all as read
        $this->actingAs($parent)
            ->post('/notifications/mark-all-read')
            ->assertRedirect();

        $this->assertSame(0, $parent->unreadNotifications()->count());
    }
}
