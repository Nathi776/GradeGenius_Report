<?php

namespace Tests\Feature;

use App\Models\TeacherMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommunicationFollowupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_parent_link_without_an_explicit_status_defaults_to_pending(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $student = User::factory()->create(['role' => 'student']);

        DB::table('parent_student_links')->insert([
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'linked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('pending', DB::table('parent_student_links')->value('status'));
    }

    public function test_replying_twice_only_notifies_the_teacher_once(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student = User::factory()->create(['role' => 'student']);
        $message = TeacherMessage::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => TeacherMessage::TYPE_PROGRESS_UPDATE,
            'subject' => 'Progress update',
            'body' => 'A short progress update about your child.',
        ]);

        $this->actingAs($parent)->post("/parent/messages/{$message->id}/acknowledge", ['quick_reply' => 'Thanks, noted']);
        $this->actingAs($parent)->post("/parent/messages/{$message->id}/acknowledge", ['quick_reply' => 'Please call me']);

        $this->assertSame(1, $teacher->notifications()->count());
        $this->assertSame('Thanks, noted', $message->fresh()->quick_reply);
    }
}
