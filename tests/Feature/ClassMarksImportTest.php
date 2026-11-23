<?php

namespace Tests\Feature;

use App\Models\AssessmentResult;
use App\Models\LearnerSubject;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClassMarksImportTest extends TestCase
{
    use RefreshDatabase;

    private function classWithTwoLearners(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $first = User::factory()->create(['role' => 'student', 'email' => 'first@example.com']);
        $second = User::factory()->create(['role' => 'student', 'email' => 'second@example.com']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id, 'subject' => 'Mathematics', 'target' => 70]);
        $schoolClass->learners()->attach([$first->id, $second->id]);

        return [$teacher, $schoolClass];
    }

    private function upload(User $teacher, SchoolClass $schoolClass, string $csv)
    {
        return $this->actingAs($teacher)->post("/teacher/classes/{$schoolClass->id}/marks-import", [
            'marks_file' => UploadedFile::fake()->createWithContent('marks.csv', $csv),
        ]);
    }

    public function test_a_valid_file_imports_every_row(): void
    {
        [$teacher, $schoolClass] = $this->classWithTwoLearners();
        $date = now()->toDateString();

        $this->upload($teacher, $schoolClass, "email,type,title,mark,term,date,attempts,topic,topic_mastery\n"
            ."first@example.com,quiz,Algebra Quiz,80,3,{$date},2,Algebra,75\n"
            ."second@example.com,past paper,2025 Paper 1,64,3,{$date},,,\n")
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('import_errors');

        $this->assertSame(2, AssessmentResult::count());
        $this->assertSame(2, LearnerSubject::count());
        $this->assertSame(1, AssessmentResult::where('kind', 'past_paper')->count());
        $this->assertSame(75, LearnerSubject::whereHas('student', fn ($q) => $q->where('email', 'first@example.com'))->first()->topicMasteries()->value('mastery'));
    }

    public function test_one_bad_row_imports_nothing_and_reports_the_line(): void
    {
        [$teacher, $schoolClass] = $this->classWithTwoLearners();
        $date = now()->toDateString();

        $this->upload($teacher, $schoolClass, "email,type,title,mark,term,date\n"
            ."first@example.com,quiz,Good Quiz,80,3,{$date}\n"
            ."second@example.com,quiz,Bad Quiz,180,3,{$date}\n"
            ."stranger@example.com,quiz,Other,50,3,{$date}\n")
            ->assertSessionHas('import_errors', fn (array $errors): bool => count($errors) === 2
                && str_contains($errors[0], 'Line 3')
                && str_contains($errors[1], 'stranger@example.com'));

        $this->assertSame(0, AssessmentResult::count());
    }

    public function test_a_file_without_the_required_columns_is_rejected(): void
    {
        [$teacher, $schoolClass] = $this->classWithTwoLearners();

        $this->upload($teacher, $schoolClass, "name,score\nFirst,80\n")
            ->assertSessionHas('import_errors');

        $this->assertSame(0, AssessmentResult::count());
    }

    public function test_another_teacher_cannot_import_into_the_class(): void
    {
        [, $schoolClass] = $this->classWithTwoLearners();
        $other = User::factory()->create(['role' => 'teacher']);

        $this->upload($other, $schoolClass, "email,type,title,mark,term,date\n")->assertNotFound();
    }

    public function test_the_template_lists_the_classes_learners(): void
    {
        [$teacher, $schoolClass] = $this->classWithTwoLearners();

        $this->actingAs($teacher)->get("/teacher/classes/{$schoolClass->id}/marks-template")
            ->assertOk()
            ->assertDownload()
            ->assertSee('first@example.com')
            ->assertSee('second@example.com');
    }
}
