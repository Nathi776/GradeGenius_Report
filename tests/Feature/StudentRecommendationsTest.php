<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_page_ranks_weak_topics_and_shows_why_each_is_listed(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student/recommendations');

        $response->assertOk();
        $response->assertSee('Learning recommendations');
        $response->assertSee('Revise in this order');
        $response->assertSee('Why this is on your list');
        $response->assertSee('Suggested approach');
        $response->assertSee('Probability');
        $response->assertSee('below the 65% mark');
        $response->assertSee(route('student.report').'#subject-mathematics', false);
    }

    public function test_a_close_exam_is_shown_and_named_in_the_reasons(): void
    {
        $this->withoutVite();
        Exam::factory()->forSubject('mathematics', 'Mathematics Test', 4)->create(['paper' => 'Paper 1']);

        $response = $this->actingAs($this->student())->get('/student/recommendations');

        $response->assertSee('Mathematics Test');
        $response->assertSee('In 4 days');
        $response->assertSee('is in 4 days', false);
    }

    public function test_without_exams_the_page_says_so_and_still_works(): void
    {
        $this->withoutVite();

        $this->actingAs($this->student())->get('/student/recommendations')
            ->assertOk()
            ->assertSee('No exam schedule is available yet')
            ->assertSee('Study planner')
            ->assertSee('Open the planner');
    }

    public function test_sidebar_links_to_the_page_and_marks_it_current(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->student())->get('/student/recommendations');

        $response->assertSee(route('student.recommendations'), false);
        $response->assertSee('aria-current="page"', false);
        $response->assertDontSee('href="#"', false);
    }

    public function test_only_verified_students_can_open_the_page(): void
    {
        $this->get('/student/recommendations')->assertRedirectToRoute('login');

        $this->actingAs(User::factory()->unverified()->create(['role' => 'student']))
            ->get('/student/recommendations')
            ->assertRedirectToRoute('verification.notice');

        foreach (['parent', 'teacher', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/student/recommendations')
                ->assertForbidden();
        }
    }
}
