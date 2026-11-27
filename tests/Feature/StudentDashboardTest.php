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

    public function test_student_home_shows_the_learner_profile_and_every_subject(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student(['name' => 'Lerato Mokoena']))->get('/student');

        $response->assertOk();
        $response->assertSee('Lerato Mokoena');
        $response->assertSee('Student portal');
        $response->assertDontSee('Sample academic profile');
        foreach (DemoReportData::forStudent()['subjects'] as $subject) {
            $response->assertSee($subject['name']);
        }
    }

    public function test_student_home_shows_the_planned_sections_and_honest_empty_states(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student');

        $response->assertSee('Your next learning actions');
        $response->assertSee('My enrolled subjects');
        $response->assertSee('Recent assessment activity');
        $response->assertSee('Study planner');
        $response->assertSee('Upcoming exams');
        $response->assertSee('Coming soon');
        $response->assertSee('No exam schedule is available yet');
        $response->assertSee('Study streak');
        $response->assertSee('Weekly study goal');
        $response->assertSee('Practice exams taken');
        $response->assertSee('Activity sharing:');
        $response->assertDontSee('Current login session');
        $response->assertDontSee('data-session-timer', false);
        $response->assertDontSee('Study notes read');
        $response->assertDontSee('Parent account connection');
        $response->assertDontSee('Unread Note');
        $response->assertDontSee('Pending Quiz');
    }

    public function test_student_can_link_a_verified_parent_from_settings(): void
    {
        $parent = User::factory()->create(['role' => 'parent', 'email' => 'guardian@example.com']);
        $student = $this->student();

        $this->actingAs($student)->post('/student/guardians', ['parent_email' => $parent->email])
            ->assertRedirect();

        $this->assertTrue($student->parents()->whereKey($parent->id)->exists());
        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee($student->email);
    }

    public function test_subject_cards_show_report_metrics_and_link_to_the_existing_detail_table(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student');

        $response->assertSee('Quizzes');
        $response->assertSee('Past papers');
        $response->assertSee('Topic to practise');
        $response->assertSee('View subject report');
        $response->assertSee(route('student.report').'#subject-accounting', false);
        $response->assertSee('Open assessment table');
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
