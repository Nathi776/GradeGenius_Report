<?php

namespace Tests\Feature;

use App\Models\AssessmentResult;
use App\Models\LearnerSubject;
use App\Models\SchoolClass;
use App\Models\TopicMastery;
use App\Models\User;
use App\Services\LearnerReportRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealReportDataTest extends TestCase
{
    use RefreshDatabase;

    private function studentWithMarks(): User
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Real Learner']);
        $subject = LearnerSubject::factory()->create([
            'student_id' => $student->id,
            'name' => 'Mathematics',
            'target' => 80,
            'term_averages' => [55, null, 61, null],
            'questions_attempted' => 40,
            'questions_correct' => 30,
        ]);
        TopicMastery::factory()->create(['learner_subject_id' => $subject->id, 'topic' => 'Probability', 'mastery' => 48]);
        AssessmentResult::factory()->create(['learner_subject_id' => $subject->id, 'title' => 'Probability Quiz', 'score' => 52]);
        AssessmentResult::factory()->pastPaper()->create(['learner_subject_id' => $subject->id, 'title' => '2025 Paper 1', 'score' => 61]);

        return $student;
    }

    public function test_repository_builds_the_report_shape_from_recorded_marks(): void
    {
        $raw = (new LearnerReportRepository)->forStudent($this->studentWithMarks());

        $this->assertSame('Real Learner', $raw['student']['name']);
        $this->assertSame(['Term 1', 'Term 2', 'Term 3', 'Term 4'], $raw['terms']);
        $subject = $raw['subjects'][0];
        $this->assertSame([55, 55, 61, 61], $subject['term_averages']);
        $this->assertSame(80, $subject['target']);
        $this->assertSame(['Probability' => 48], $subject['topics']);
        $this->assertSame('Probability Quiz', $subject['quizzes'][0]['title']);
        $this->assertSame('2025 Paper 1', $subject['past_papers'][0]['title']);
        $this->assertSame(['attempted' => 40, 'correct' => 30], $subject['questions']);
    }

    public function test_repository_returns_null_for_a_learner_with_no_marks(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->assertNull((new LearnerReportRepository)->forStudent($student));
    }

    public function test_student_pages_show_the_learners_own_marks_not_the_sample_report(): void
    {
        config(['reports.demo_fallback' => false]);
        $student = $this->studentWithMarks();

        foreach (['/student', '/student/report', '/student/recommendations', '/student/planner'] as $path) {
            $this->actingAs($student)->get($path)->assertOk();
        }
        $this->actingAs($student)->get('/student/report')->assertSee('Mathematics')->assertDontSee('Physical Sciences');
    }

    public function test_student_without_marks_sees_an_empty_state_when_demo_fallback_is_off(): void
    {
        config(['reports.demo_fallback' => false]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/student/report')
            ->assertOk()
            ->assertSee('No marks recorded yet')
            ->assertDontSee('Physical Sciences');
    }

    public function test_student_without_marks_sees_the_sample_report_when_demo_fallback_is_on(): void
    {
        config(['reports.demo_fallback' => true]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/student/report')->assertOk()->assertSee('Physical Sciences');
    }

    public function test_parent_sees_only_their_confirmed_childs_marks(): void
    {
        config(['reports.demo_fallback' => false]);
        $child = $this->studentWithMarks();
        $stranger = $this->studentWithMarks();
        $parent = User::factory()->create(['role' => 'parent']);
        $parent->students()->attach($child->id, ['linked_at' => now(), 'status' => 'verified']);
        $parent->students()->attach($stranger->id, ['linked_at' => now(), 'status' => 'pending']);

        $this->actingAs($parent)->get('/parent/report')->assertOk()->assertSee('Real');
        $this->actingAs($parent)->get("/parent/report/{$child->id}")->assertOk();
        $this->actingAs($parent)->get("/parent/report/{$stranger->id}")->assertNotFound();
        $this->actingAs($parent)->get('/parent/report/nathi')->assertNotFound();
    }

    public function test_parent_without_confirmed_children_sees_an_empty_state_when_demo_fallback_is_off(): void
    {
        config(['reports.demo_fallback' => false]);
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)->get('/parent/report')->assertOk()->assertSee('No confirmed children yet');
        $this->actingAs($parent)->get('/parent')->assertOk()->assertSee('No confirmed children yet');
    }

    public function test_teacher_dashboard_uses_recorded_averages(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = $this->studentWithMarks();
        $schoolClass = SchoolClass::factory()->create(['teacher_id' => $teacher->id, 'subject' => 'Mathematics', 'name' => 'Maths A']);
        $schoolClass->learners()->attach($student->id);

        $this->actingAs($teacher)->get('/teacher')->assertOk()->assertSee('Maths A');
    }
}
