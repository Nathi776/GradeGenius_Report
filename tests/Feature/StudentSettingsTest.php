<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_lists_linked_parents_with_their_confirmation_state(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $confirmed = User::factory()->create(['role' => 'parent', 'name' => 'Confirmed Parent']);
        $waiting = User::factory()->create(['role' => 'parent', 'name' => 'Waiting Parent']);
        $student->parents()->attach($confirmed->id, ['status' => 'verified', 'linked_at' => now()]);
        $student->parents()->attach($waiting->id, ['status' => 'pending', 'linked_at' => now()]);

        $this->actingAs($student)->get(route('student.settings'))
            ->assertOk()
            ->assertSee('Confirmed Parent')
            ->assertSee('(confirmed)')
            ->assertSee('Waiting Parent')
            ->assertSee('waiting for parent to confirm');
    }

    public function test_only_students_can_open_student_settings(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)->get(route('student.settings'))->assertForbidden();
    }

    public function test_linking_an_already_confirmed_parent_does_not_reset_the_link(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $parent = User::factory()->create(['role' => 'parent']);
        $student->parents()->attach($parent->id, ['status' => 'verified', 'linked_at' => now()]);

        $this->actingAs($student)->post(route('student.guardians.store'), ['parent_email' => $parent->email])
            ->assertSessionHas('status', 'That parent account is already linked to you.');

        $this->assertSame('verified', $student->parents()->first()->pivot->status);
    }

    public function test_dashboard_trend_shows_a_message_instead_of_inventing_terms(): void
    {
        config(['reports.demo_fallback' => false]);
        $student = User::factory()->create(['role' => 'student']);
        $subject = $student->learnerSubjects()->create([
            'name' => 'Mathematics',
            'target' => 65,
            'term_averages' => [null, null, null, 45],
            'questions_attempted' => 0,
            'questions_correct' => 0,
        ]);
        $this->assertNotNull($subject->id);

        $this->actingAs($student)->get('/student')
            ->assertOk()
            ->assertSee('Marks from at least two terms are needed to show a trend')
            ->assertDontSee('<title>Overall, Term 1', false);
    }
}
