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
        foreach (['Suggested next step', 'Academic progress', 'Going well', 'Learning support', 'Recent activity', 'Upcoming assessments', 'Study planner', 'Notifications', 'Coming soon'] as $text) {
            $response->assertSee($text);
        }
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
