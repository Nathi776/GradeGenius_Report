<?php

namespace Tests\Feature;

use App\Models\AssessmentResult;
use App\Models\LearnerSubject;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordLearnerResultTest extends TestCase
{
    use RefreshDatabase;

    private function classWithLearner(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id, 'subject' => 'Mathematics', 'target' => 70]);
        $schoolClass->learners()->attach($student->id);

        return [$teacher, $student, $schoolClass];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'kind' => 'quiz',
            'title' => 'Algebra Quiz',
            'score' => 80,
            'term' => 3,
            'taken_on' => now()->toDateString(),
            'attempts' => 2,
        ], $overrides);
    }

    public function test_teacher_records_a_mark_and_term_averages_update(): void
    {
        [$teacher, $student, $schoolClass] = $this->classWithLearner();
        $url = "/teacher/classes/{$schoolClass->id}/learners/{$student->id}/results";

        $this->actingAs($teacher)->post($url, $this->payload(['score' => 80]))->assertSessionHasNoErrors();
        $this->actingAs($teacher)->post($url, $this->payload(['score' => 60, 'title' => 'Second Quiz']))->assertSessionHasNoErrors();

        $subject = LearnerSubject::firstOrFail();
        $this->assertSame('Mathematics', $subject->name);
        $this->assertSame(70, $subject->target);
        $this->assertSame([null, null, 70, null], $subject->term_averages);
        $this->assertSame(2, AssessmentResult::count());
        $this->assertSame($teacher->id, AssessmentResult::first()->recorded_by);
    }

    public function test_topic_and_question_totals_are_recorded(): void
    {
        [$teacher, $student, $schoolClass] = $this->classWithLearner();

        $this->actingAs($teacher)->post("/teacher/classes/{$schoolClass->id}/learners/{$student->id}/results", $this->payload([
            'topic' => 'Probability',
            'mastery' => 45,
            'questions_attempted' => 20,
            'questions_correct' => 12,
        ]))->assertSessionHasNoErrors();

        $subject = LearnerSubject::firstOrFail();
        $this->assertSame(45, $subject->topicMasteries()->where('topic', 'Probability')->value('mastery'));
        $this->assertSame(20, $subject->questions_attempted);
        $this->assertSame(12, $subject->questions_correct);
    }

    public function test_invalid_marks_are_rejected(): void
    {
        [$teacher, $student, $schoolClass] = $this->classWithLearner();
        $url = "/teacher/classes/{$schoolClass->id}/learners/{$student->id}/results";

        $this->actingAs($teacher)->post($url, $this->payload(['score' => 140, 'term' => 9, 'taken_on' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors(['score', 'term', 'taken_on']);
        $this->actingAs($teacher)->post($url, $this->payload(['topic' => 'Probability']))->assertSessionHasErrors('mastery');
        $this->actingAs($teacher)->post($url, $this->payload(['questions_attempted' => 5, 'questions_correct' => 9]))->assertSessionHasErrors('questions_correct');
        $this->assertSame(0, AssessmentResult::count());
    }

    public function test_teacher_cannot_record_marks_for_another_teachers_class_or_a_learner_outside_the_class(): void
    {
        [, $student, $schoolClass] = $this->classWithLearner();
        $other = User::factory()->create(['role' => 'teacher']);
        [$owner] = [$schoolClass->teacher];
        $outsider = User::factory()->create(['role' => 'student']);

        $this->actingAs($other)->post("/teacher/classes/{$schoolClass->id}/learners/{$student->id}/results", $this->payload())->assertNotFound();
        $this->actingAs($owner)->post("/teacher/classes/{$schoolClass->id}/learners/{$outsider->id}/results", $this->payload())->assertNotFound();
        $this->assertSame(0, AssessmentResult::count());
    }

    public function test_the_record_form_renders_for_the_class_owner(): void
    {
        [$teacher, $student, $schoolClass] = $this->classWithLearner();

        $this->actingAs($teacher)->get("/teacher/classes/{$schoolClass->id}/learners/{$student->id}/results/create")
            ->assertOk()
            ->assertSee('Record a mark for '.$student->name);
    }
}
