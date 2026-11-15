<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DemoReportData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function student(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'student']);
    }

    public function test_student_home_greets_the_learner_and_shows_every_subject(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student(['name' => 'Lerato Mokoena']))->get('/student');

        $response->assertOk();
        $response->assertSee('Welcome back, Lerato');
        $response->assertDontSee('Mokoena');
        foreach (DemoReportData::forStudent()['subjects'] as $subject) {
            $response->assertSee($subject['name']);
        }
    }

    public function test_student_home_shows_the_planned_sections_and_honest_empty_states(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student');

        $response->assertSee('Your focus');
        $response->assertSee('Your next learning actions');
        $response->assertSee('Subject overview');
        $response->assertSee('Recent activity');
        $response->assertSee('Study planner');
        $response->assertSee('Upcoming exams');
        $response->assertSee('Coming soon');
        $response->assertSee('No exams scheduled yet');
    }

    public function test_learning_actions_and_subject_links_point_at_the_academic_performance_page(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student');

        $response->assertSee(route('student.report').'#subject-accounting', false);
        $response->assertSee(route('student.report').'#subjects-heading', false);
    }

    public function test_sidebar_marks_the_current_page_and_never_links_unbuilt_pages(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student');

        $response->assertSee('Student workspace');
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Academic Performance');
        $response->assertSee('Study Planner');
        $response->assertSee('aria-disabled="true"', false);
        $response->assertDontSee('href="#"', false);
    }

    public function test_academic_performance_keeps_the_full_report_inside_the_sidebar_layout(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student/report');

        $response->assertOk();
        $response->assertSee('Student workspace');
        $response->assertSee('Assessment performance');
        $response->assertSee('data-subject-card');
    }

    public function test_the_dashboard_route_sends_students_to_the_new_home(): void
    {
        $this->actingAs($this->student())
            ->get('/dashboard')
            ->assertRedirectToRoute('student.home');
    }

    public function test_only_verified_students_can_open_the_student_home(): void
    {
        $this->get('/student')->assertRedirectToRoute('login');

        $this->actingAs(User::factory()->unverified()->create(['role' => 'student']))
            ->get('/student')
            ->assertRedirectToRoute('verification.notice');

        foreach (['parent', 'teacher', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/student')
                ->assertForbidden();
        }
    }
}
