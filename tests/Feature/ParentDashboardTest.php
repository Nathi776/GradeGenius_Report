<?php

namespace Tests\Feature;

use App\Models\StudySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function parent(): User
    {
        return User::factory()->create(['role' => 'parent']);
    }

    public function test_parent_home_shows_the_first_child_and_the_planned_sections(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->parent())->get('/parent');

        $response->assertOk();
        $response->assertSee("Nathi's progress", false);
        $response->assertSee('Parent workspace');
        foreach (['Verified guardian account', 'Nathi', 'Sipho', 'Suggested next step', 'Academic progress', 'Going well', 'Subject performance and learning support', 'Recorded learning activity', 'assessments'] as $text) {
            $response->assertSee($text);
        }
        foreach (['Linked learner sessions', 'Sample academic reports', 'Refresh learner activity', 'Select registered learner', 'Coming soon', 'Active telemetry profile'] as $text) {
            $response->assertDontSee($text);
        }
    }

    public function test_parent_sees_real_activity_only_for_a_student_linked_to_their_account(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = User::factory()->create(['name' => 'Linked Learner', 'email' => 'linked-student@example.com', 'shares_activity' => true]);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($student)->get('/student')->assertOk();

        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('Learner activity')
            ->assertSee('View sessions');

        $this->actingAs($parent)->get(route('parent.activity'))
            ->assertOk()
            ->assertSee('Recent sessions')
            ->assertSee('Student dashboard')
            ->assertSee('Online')
            ->assertSee('Last request');

        $this->actingAs($this->parent())->get(route('parent.activity'))
            ->assertOk()
            ->assertDontSee('Linked Learner');
    }

    public function test_parent_can_switch_children_without_signing_out(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->parent())->get('/parent?child=sipho');

        $response->assertOk();
        $response->assertSee("Sipho's progress", false);
        $response->assertSee('Your children');
        $response->assertSee(route('parent.home', ['child' => 'nathi']), false);
    }

    public function test_parent_can_open_activity_only_for_a_linked_child(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $first = User::factory()->create(['name' => 'First Linked Learner', 'role' => 'student', 'shares_activity' => true]);
        $second = User::factory()->create(['name' => 'Selected Linked Learner', 'role' => 'student', 'shares_activity' => true]);
        $parent->students()->attach([$first->id, $second->id], ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($parent)->get(route('parent.activity', ['child' => $second->id]))
            ->assertOk()
            ->assertSee('Selected Linked Learner');

        $this->get(route('parent.activity', ['child' => User::factory()->create(['role' => 'student'])->id]))
            ->assertNotFound();
        $this->get(route('parent.activity', ['child' => 'abc']))->assertNotFound();
    }

    public function test_parent_sees_real_page_visits_but_not_unrecorded_study_actions(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = User::factory()->create(['role' => 'student', 'shares_activity' => true]);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($student)->get('/student')->assertOk();
        $response = $this->actingAs($parent)->get(route('parent.activity'));

        $response->assertOk()
            ->assertSee('Student dashboard')
            ->assertDontSee('Opened Past Paper Memo')
            ->assertDontSee('IP Location:');
    }

    public function test_every_confirmed_child_is_listed_even_without_recorded_marks(): void
    {
        $this->withoutVite();
        config(['reports.demo_fallback' => false]);
        $parent = $this->parent();
        $withMarks = User::factory()->create(['role' => 'student', 'name' => 'Marked Learner']);
        $withoutMarks = User::factory()->create(['role' => 'student', 'name' => 'Unmarked Learner']);
        $withMarks->learnerSubjects()->create(['name' => 'Mathematics', 'target' => 65, 'term_averages' => [50, 55, null, null], 'questions_attempted' => 0, 'questions_correct' => 0]);
        $parent->students()->attach([$withMarks->id, $withoutMarks->id], ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('Marked Learner')
            ->assertSee('Unmarked Learner')
            ->assertSee('No marks yet');

        $this->actingAs($parent)->get('/parent?child='.$withoutMarks->id)
            ->assertOk()
            ->assertSee('No marks have been recorded for Unmarked yet.')
            ->assertDontSee('Suggested next step');
    }

    public function test_parent_with_children_but_no_marks_gets_a_page_not_an_error(): void
    {
        $this->withoutVite();
        config(['reports.demo_fallback' => false]);
        $parent = $this->parent();
        $child = User::factory()->create(['role' => 'student', 'name' => 'Only Child']);
        $parent->students()->attach($child->id, ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('No marks have been recorded for Only yet.');
    }

    public function test_private_learner_study_figures_are_not_exposed_to_the_parent(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = User::factory()->create(['role' => 'student', 'shares_activity' => false]);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => 'verified']);
        StudySession::factory()->done()->create(['user_id' => $student->id, 'planned_date' => today(), 'actual_minutes' => 47, 'is_counted' => true]);

        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('has not shared activity with you')
            ->assertDontSee('47 min')
            ->assertDontSee('Study streak');

        $this->actingAs($parent)->get(route('parent.activity'))
            ->assertOk()
            ->assertSee('has not shared activity with you')
            ->assertDontSee('Verified study time');
    }

    public function test_shared_study_figures_appear_when_the_learner_opts_in(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = User::factory()->create(['role' => 'student', 'shares_activity' => true]);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => 'verified']);
        StudySession::factory()->done()->create(['user_id' => $student->id, 'planned_date' => today(), 'actual_minutes' => 47, 'is_counted' => true]);

        $this->actingAs($parent)->get(route('parent.activity'))
            ->assertOk()
            ->assertSee('47 min')
            ->assertSee('Study streak');
    }

    public function test_a_single_recorded_term_is_not_reported_as_zero_change(): void
    {
        $this->withoutVite();
        config(['reports.demo_fallback' => false]);
        $parent = $this->parent();
        $student = User::factory()->create(['role' => 'student', 'name' => 'Sole Term']);
        $student->learnerSubjects()->create(['name' => 'Mathematics', 'target' => 65, 'term_averages' => [null, null, null, 45], 'questions_attempted' => 0, 'questions_correct' => 0]);
        $parent->students()->attach($student->id, ['linked_at' => now(), 'status' => 'verified']);

        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('no earlier recorded term to compare with')
            ->assertSee('Not yet')
            ->assertDontSee('0 pts');
    }

    public function test_switcher_preserves_the_selected_term_and_shows_report_metrics(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->parent())->get('/parent?child=nathi&term=2');

        $response->assertOk();
        $response->assertSee('child=sipho&amp;term=2', false);
        $response->assertSee('average');
        $response->assertSee('Subjects at goal');
    }

    public function test_unknown_child_is_not_found(): void
    {
        $this->actingAs($this->parent())->get('/parent?child=someone-else')->assertNotFound();
    }

    public function test_sidebar_never_links_unbuilt_pages(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->parent())->get('/parent');

        $response->assertSee('aria-current="page"', false);
        $response->assertSee('aria-disabled="true"', false);
        $response->assertDontSee('href="#"', false);
    }

    public function test_detailed_report_links_back_to_the_dashboard_for_parents(): void
    {
        $this->withoutVite();

        $this->actingAs($this->parent())->get('/parent/reports')
            ->assertOk()
            ->assertSee('Back to parent dashboard');
        $this->get('/parent/report')->assertOk();
    }

    public function test_parent_report_route_requires_parent_authorization(): void
    {
        $this->get('/parent/report')->assertRedirectToRoute('login');

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get('/parent/report')
            ->assertForbidden();
    }

    public function test_only_verified_parents_can_open_the_parent_home(): void
    {
        $this->get('/parent')->assertRedirectToRoute('login');

        $this->actingAs(User::factory()->unverified()->create(['role' => 'parent']))
            ->get('/parent')
            ->assertRedirectToRoute('verification.notice');

        foreach (['student', 'teacher', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/parent')
                ->assertForbidden();
        }
    }

    public function test_the_dashboard_route_sends_parents_to_the_new_home(): void
    {
        $this->actingAs($this->parent())->get('/dashboard')->assertRedirectToRoute('parent.home');
    }
}
