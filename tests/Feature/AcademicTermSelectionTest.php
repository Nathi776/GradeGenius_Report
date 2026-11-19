<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicTermSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_student_dashboard_and_report_use_the_selected_term_average(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/student?term=1')
            ->assertOk()
            ->assertSee('Term 2')
            ->assertSee('67%')
            ->assertSee('Quizzes taken')
            ->assertSee('Past papers')
            ->assertSee(route('student.report', ['term' => '1']), false);

        $this->get('/student/report?term=1')
            ->assertOk()
            ->assertSee('Term 2')
            ->assertSee('67%');
    }

    public function test_parent_dashboard_uses_the_selected_child_term_average(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'parent']))
            ->get('/parent?child=nathi&term=0')
            ->assertOk()
            ->assertSee('Term 1')
            ->assertSee('63%')
            ->assertSee('Learning activity overview')
            ->assertSee('Active weeks')
            ->assertSee('study time and educator remarks are not recorded', false);
    }

    public function test_teacher_shows_term_marks_and_hides_unavailable_engagement_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'teacher']))
            ->get('/teacher?term=0')
            ->assertOk()
            ->assertSee('Term 1 Reports')
            ->assertSee('Academic Term:</span> Term 1', false)
            ->assertSee('Term 1:')
            ->assertSee('Quiz scores, learner activity, and study-note engagement are only available for Term 4', false)
            ->assertSee('data-term-unavailable', false);

        $latestTerm = count(DemoReportData::classesForTeacher()['terms']) - 1;
        $this->get('/teacher?term='.$latestTerm)
            ->assertOk()
            ->assertSee('Term 4 Reports')
            ->assertSee('Academic Term:</span> Term 4', false)
            ->assertDontSee('data-term-four-only hidden', false);
    }

    public function test_teacher_class_report_uses_the_selected_term_average(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'teacher']))
            ->get('/teacher/reports?term=1')
            ->assertOk()
            ->assertSee('Academic Term:</span> Term 2', false)
            ->assertSee('Term 2')
            ->assertSee('61%');
    }

    public function test_administrator_school_average_uses_the_selected_term(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'administrator']))
            ->get('/administrator?term=1')
            ->assertOk()
            ->assertSee('Academic Term:</span> Term 2', false)
            ->assertSee('Term 2')
            ->assertSee('65.2%')
            ->assertSee('latest available sample');
    }

    public function test_unknown_term_selection_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get('/student?term=9')
            ->assertNotFound();
    }

    public function test_student_report_service_preserves_all_terms_and_selects_a_term(): void
    {
        $reports = app(StudentReportService::class);
        $raw = DemoReportData::forStudent();
        $allTerms = $reports->build($raw);
        $termTwo = $reports->build($raw, 1);

        $this->assertSame(74, $allTerms['overall']['current']);
        $this->assertSame(11, $allTerms['overall']['change']);
        $this->assertTrue($allTerms['all_terms_selected']);
        $this->assertSame('Term 2', $termTwo['overall']['current_term']);
        $this->assertSame(67, $termTwo['overall']['current']);
        $this->assertSame(68, $termTwo['subjects'][0]['current']);
        $this->assertFalse($termTwo['all_terms_selected']);
    }
}
