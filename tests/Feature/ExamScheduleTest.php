<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use App\Services\ExamScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_only_upcoming_exams_for_the_grade_are_returned_soonest_first(): void
    {
        Exam::factory()->forSubject('accounting', 'Accounting Exam', 20)->create();
        Exam::factory()->forSubject('mathematics', 'Maths Test', 3)->create();
        Exam::factory()->forSubject('mathematics', 'Maths Past Test', -2)->create();
        Exam::factory()->forSubject('mathematics', 'Grade 10 Test', 4, 10)->create();

        $rows = app(ExamScheduleService::class)->upcomingForGrade(12, ['mathematics' => 'Mathematics']);

        $this->assertSame(['Maths Test', 'Accounting Exam'], array_column($rows, 'title'));
        $this->assertSame('Mathematics', $rows[0]['subject']);
        $this->assertSame('Accounting', $rows[1]['subject']);
        $this->assertSame(3, $rows[0]['days_until']);
        $this->assertSame('In 3 days', $rows[0]['countdown']);
    }

    public function test_countdown_wording_for_today_and_tomorrow(): void
    {
        Exam::factory()->forSubject('mathematics', 'Today Test', 0)->create();
        Exam::factory()->forSubject('accounting', 'Tomorrow Test', 1)->create();

        $rows = app(ExamScheduleService::class)->upcomingForGrade(12);

        $this->assertSame(['Today', 'Tomorrow'], array_column($rows, 'countdown'));
    }

    public function test_student_home_lists_upcoming_exams_with_a_countdown(): void
    {
        $this->withoutVite();
        Exam::factory()->forSubject('mathematics', 'Mathematics Test', 6)->create(['paper' => 'Paper 1']);

        $response = $this->actingAs($this->student())->get('/student');

        $response->assertOk();
        $response->assertSee('Mathematics Test');
        $response->assertSee('In 6 days');
        $response->assertDontSee('No exam schedule is available yet');
    }

    public function test_student_home_keeps_the_honest_empty_state_without_exams(): void
    {
        $this->withoutVite();

        $this->actingAs($this->student())->get('/student')
            ->assertOk()
            ->assertSee('No exam schedule is available yet');
    }
}
