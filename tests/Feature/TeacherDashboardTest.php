<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'teacher']);
    }

    public function test_teacher_home_shows_the_sections_and_honest_empty_states(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->teacher(['name' => 'Mrs Naidoo']))->get('/teacher');

        $response->assertOk();
        $response->assertSee('Welcome back, Mrs Naidoo');
        $response->assertSee('Teacher workspace');
        foreach (['Class performance trend', 'My classes', 'Learners to review', 'Learner insights', 'Upcoming assessments', 'Recent activity', 'Coming soon', 'Demo data'] as $text) {
            $response->assertSee($text);
        }
        $response->assertSee('Mathematics');
    }

    public function test_review_flags_state_their_criteria_and_reasons(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->teacher())->get('/teacher');

        $response->assertSee('below 50%');
        $response->assertSee('not a judgement of ability');
        $response->assertSee('since last term');
    }

    public function test_sidebar_links_only_to_built_pages(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->teacher())->get('/teacher');

        $response->assertSee('aria-current="page"', false);
        $response->assertSee('aria-disabled="true"', false);
        $response->assertSee(route('teacher.reports'), false);
        $response->assertDontSee('href="#"', false);
    }

    public function test_existing_class_report_still_works_and_links_back(): void
    {
        $this->withoutVite();

        $this->actingAs($this->teacher())->get('/teacher/reports')
            ->assertOk()
            ->assertSee('Class Performance Report')
            ->assertSee('Back to teacher dashboard');
    }

    public function test_only_verified_teachers_can_open_the_teacher_home(): void
    {
        $this->get('/teacher')->assertRedirectToRoute('login');

        $this->actingAs(User::factory()->unverified()->create(['role' => 'teacher']))
            ->get('/teacher')
            ->assertRedirectToRoute('verification.notice');

        foreach (['student', 'parent', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/teacher')
                ->assertForbidden();
        }
    }

    public function test_the_dashboard_route_sends_teachers_to_the_new_home(): void
    {
        $this->actingAs($this->teacher())->get('/dashboard')->assertRedirectToRoute('teacher.home');
    }
}
