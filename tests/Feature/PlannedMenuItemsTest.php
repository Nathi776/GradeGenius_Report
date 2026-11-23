<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlannedMenuItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_planned_menu_items_are_shown_when_enabled(): void
    {
        config(['reports.show_planned_menu_items' => true]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/student/report')->assertSee('Past Papers')->assertSee('Soon');
    }

    public function test_planned_menu_items_are_hidden_when_disabled(): void
    {
        config(['reports.show_planned_menu_items' => false]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/student/report')
            ->assertOk()
            ->assertSee('Study Planner')
            ->assertDontSee('Past Papers')
            ->assertDontSee('Soon');
    }
}
