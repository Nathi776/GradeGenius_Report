<?php

namespace Tests\Feature;

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
        foreach (['Verified guardian account', 'Linked learner sessions', 'No student account is linked yet', 'Sample academic reports', 'Nathi', 'Sipho', 'Suggested next step', 'Academic progress', 'Going well', 'Subject performance and learning support', 'Educator remarks are not recorded', 'Recorded learning activity', 'assessments', 'Upcoming assessments', 'Study planner', 'Notifications', 'Coming soon'] as $text) {
            $response->assertSee($text);
        }
    }

    public function test_parent_sees_real_activity_only_for_a_student_linked_to_their_account(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = User::factory()->create(['name' => 'Linked Learner', 'email' => 'linked-student@example.com']);
        $parent->students()->attach($student->id, ['linked_at' => now()]);

        $this->actingAs($student)->get('/student')->assertOk();
        $this->actingAs($parent)->get('/parent')
            ->assertOk()
            ->assertSee('linked-student@example.com')
            ->assertSee('Student dashboard')
            ->assertSee('Select registered learner')
            ->assertSee('Grade and school are not recorded')
            ->assertSee('1 login · 0h 0m')
            ->assertSee('Online')
            ->assertSee('Viewing telemetry')
            ->assertSee('Active telemetry profile')
            ->assertSee('Recorded visits')
            ->assertSee('Last request');

        $this->actingAs($this->parent())->get('/parent')
            ->assertDontSee('linked-student@example.com');
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

    public function test_parent_can_select_only_a_linked_learner_for_telemetry(): void
    {
        $parent = $this->parent();
        $firstStudent = User::factory()->create(['name' => 'First Linked Learner', 'role' => 'student']);
        $secondStudent = User::factory()->create(['name' => 'Selected Linked Learner', 'role' => 'student']);
        $parent->students()->attach([$firstStudent->id, $secondStudent->id], ['linked_at' => now()]);

        $this->actingAs($parent)->get('/parent?learner='.$secondStudent->id)
            ->assertOk()
            ->assertSee('Active telemetry profile')
            ->assertSee('Selected Linked Learner')
            ->assertSee(route('parent.home', ['child' => 'nathi', 'term' => 'all', 'learner' => $secondStudent->id]));

        $this->get('/parent?learner='.User::factory()->create(['role' => 'student'])->id)
            ->assertNotFound();
    }

    public function test_parent_sees_real_page_visits_but_not_unrecorded_study_actions(): void
    {
        $this->withoutVite();
        $parent = $this->parent();
        $student = User::factory()->create(['role' => 'student']);
        $parent->students()->attach($student->id, ['linked_at' => now()]);

        $this->actingAs($student)->get('/student')->assertOk();
        $response = $this->actingAs($parent)->get('/parent');

        $response->assertOk()
            ->assertSee('Student dashboard')
            ->assertDontSee('Opened Past Paper Memo')
            ->assertDontSee('IP Location:');
    }

    public function test_switcher_preserves_the_selected_term_and_shows_report_metrics(): void
    {
        $this->withoutVite();

        $response = $this->actingAs($this->parent())->get('/parent?child=nathi&term=2');

        $response->assertOk();
        $response->assertSee('child=sipho&amp;term=2', false);
        $response->assertSee('average');
        $response->assertSee('subjects at goal');
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
