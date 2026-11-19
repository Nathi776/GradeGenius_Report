<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_report_shows_the_first_child_with_every_subject(): void
    {
        $this->withoutVite();

        $response = $this->actingAs(User::factory()->create(['role' => 'parent']))->get('/parent/report');
        $response->assertOk();
        $response->assertSee("Nathi's progress", false);
        foreach (['Mathematics', 'Physical Sciences', 'English Home Language', 'Life Sciences', 'Accounting'] as $subject) {
            $response->assertSee($subject);
        }
        $response->assertSee('Needs support');
        $response->assertSee('Suggested next step');
    }

    public function test_parent_can_switch_to_another_child(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create(['role' => 'parent']))
            ->get('/parent/report/sipho')->assertOk()->assertSee('Sipho');
    }

    public function test_unknown_child_is_not_found(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create(['role' => 'parent']))
            ->get('/parent/report/someone-else')->assertNotFound();
    }
}
