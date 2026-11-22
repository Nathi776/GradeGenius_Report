<?php

namespace Tests\Feature;

use App\Mail\ClassAnnouncementMail;
use App\Models\ClassAnnouncement;
use App\Models\SchoolClass;
use App\Models\User;
use App\Notifications\ClassAnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClassAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private function classWithLearner(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id]);
        $schoolClass->learners()->attach($student->id);

        return [$teacher, $student, $schoolClass];
    }

    private function linkParent(User $student, string $status): User
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => $status]);

        return $parent;
    }

    /**
     * @return array<string, string>
     */
    private function payload(SchoolClass $schoolClass): array
    {
        return [
            'school_class_id' => (string) $schoolClass->id,
            'title' => 'Test on Friday',
            'body' => 'Please make sure your child revises chapters 3 and 4.',
            'priority' => 'important',
        ];
    }

    public function test_announcement_reaches_only_confirmed_guardians_of_the_class(): void
    {
        Notification::fake();
        Mail::fake();
        [$teacher, $student, $schoolClass] = $this->classWithLearner();
        $confirmed = $this->linkParent($student, 'verified');
        $pending = $this->linkParent($student, 'pending');
        $unrelated = User::factory()->create(['role' => 'parent']);

        $this->actingAs($teacher)->post('/teacher/announcements', $this->payload($schoolClass))
            ->assertRedirect(route('teacher.announcements.index'));

        Notification::assertSentTo($confirmed, ClassAnnouncementNotification::class);
        Notification::assertNotSentTo([$pending, $unrelated], ClassAnnouncementNotification::class);
        Mail::assertSent(ClassAnnouncementMail::class, 1);
        $this->assertSame(1, ClassAnnouncement::first()->recipients_count);
    }

    public function test_guardian_of_two_learners_in_one_class_is_notified_once(): void
    {
        Notification::fake();
        Mail::fake();
        [$teacher, $student, $schoolClass] = $this->classWithLearner();
        $secondStudent = User::factory()->create(['role' => 'student']);
        $schoolClass->learners()->attach($secondStudent->id);
        $parent = $this->linkParent($student, 'verified');
        $parent->students()->attach($secondStudent->id, ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($teacher)->post('/teacher/announcements', $this->payload($schoolClass));

        Notification::assertSentToTimes($parent, ClassAnnouncementNotification::class, 1);
    }

    public function test_teacher_cannot_announce_to_a_class_they_do_not_teach(): void
    {
        [, $student, $schoolClass] = $this->classWithLearner();
        $this->linkParent($student, 'verified');
        $otherTeacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($otherTeacher)->post('/teacher/announcements', $this->payload($schoolClass))
            ->assertForbidden();

        $this->assertSame(0, ClassAnnouncement::count());
    }

    public function test_class_without_confirmed_guardians_is_rejected(): void
    {
        [$teacher, $student, $schoolClass] = $this->classWithLearner();
        $this->linkParent($student, 'pending');

        $this->actingAs($teacher)->post('/teacher/announcements', $this->payload($schoolClass))
            ->assertSessionHasErrors('school_class_id');

        $this->assertSame(0, ClassAnnouncement::count());
    }

    public function test_parent_can_read_an_announcement_for_their_childs_class_and_it_is_marked_read(): void
    {
        Mail::fake();
        [$teacher, $student, $schoolClass] = $this->classWithLearner();
        $parent = $this->linkParent($student, 'verified');
        $this->actingAs($teacher)->post('/teacher/announcements', $this->payload($schoolClass));
        $announcement = ClassAnnouncement::firstOrFail();
        $this->assertSame(1, $parent->unreadNotifications()->count());

        $this->actingAs($parent)->get("/parent/announcements/{$announcement->id}")
            ->assertOk()
            ->assertSee('Test on Friday');

        $this->assertSame(0, $parent->unreadNotifications()->count());
    }

    public function test_parent_without_a_child_in_the_class_cannot_read_the_announcement(): void
    {
        [, , $schoolClass] = $this->classWithLearner();
        $announcement = ClassAnnouncement::factory()->create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $schoolClass->teacher_id,
        ]);
        $outsider = User::factory()->create(['role' => 'parent']);

        $this->actingAs($outsider)->get("/parent/announcements/{$announcement->id}")->assertForbidden();
    }
}
