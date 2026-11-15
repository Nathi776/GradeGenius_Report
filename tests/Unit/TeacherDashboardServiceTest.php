<?php

namespace Tests\Unit;

use App\Services\TeacherDashboardService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class TeacherDashboardServiceTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function build(?array $data = null): array
    {
        return (new TeacherDashboardService)->build($data ?? DemoReportData::classesForTeacher());
    }

    public function test_class_average_is_the_mean_of_each_learners_latest_average(): void
    {
        $data = DemoReportData::classesForTeacher();
        $dashboard = $this->build($data);

        $first = $data['classes'][0];
        $latest = array_map(fn (array $l): int => end($l['term_averages']), $first['learners']);

        $this->assertSame((int) round(array_sum($latest) / count($latest)), $dashboard['classes'][0]['average']);
        $this->assertSame(count($first['learners']), $dashboard['classes'][0]['learner_count']);
    }

    public function test_total_learners_counts_every_enrolled_learner_once(): void
    {
        $data = DemoReportData::classesForTeacher();
        $ids = [];
        foreach ($data['classes'] as $class) {
            foreach ($class['learners'] as $learner) {
                $ids[$learner['id']] = true;
            }
        }

        $this->assertSame(count($ids), $this->build($data)['snapshot']['students']);
    }

    public function test_review_flags_follow_the_explicit_criteria_and_carry_their_reasons(): void
    {
        $dashboard = $this->build();

        $this->assertNotEmpty($dashboard['review']);
        foreach ($dashboard['review'] as $learner) {
            $this->assertNotEmpty($learner['reasons']);
            $meetsRule = $learner['average'] < TeacherDashboardService::RISK_BELOW
                || str_contains(implode(' ', $learner['reasons']), 'since last term');
            $this->assertTrue($meetsRule);
        }
    }

    public function test_a_learner_with_a_steady_pass_is_not_flagged(): void
    {
        $data = ['teacher' => ['name' => 'T'], 'terms' => ['Term 1', 'Term 2'], 'classes' => [[
            'key' => 'c', 'name' => 'Grade 10', 'subject' => 'Maths', 'grade' => 10, 'target' => 60,
            'learners' => [['id' => 'x1', 'name' => 'Steady', 'term_averages' => [55, 56]]],
        ]]];

        $dashboard = $this->build($data);

        $this->assertSame(0, $dashboard['snapshot']['at_risk']);
        $this->assertSame([], $dashboard['review']);
    }

    public function test_a_drop_of_five_points_is_flagged_even_when_the_average_is_fine(): void
    {
        $data = ['teacher' => ['name' => 'T'], 'terms' => ['Term 1', 'Term 2'], 'classes' => [[
            'key' => 'c', 'name' => 'Grade 10', 'subject' => 'Maths', 'grade' => 10, 'target' => 60,
            'learners' => [['id' => 'x1', 'name' => 'Slipping', 'term_averages' => [80, 74]]],
        ]]];

        $this->assertSame(1, $this->build($data)['snapshot']['at_risk']);
    }

    public function test_top_performers_meet_the_published_threshold(): void
    {
        $dashboard = $this->build();

        $this->assertGreaterThan(0, $dashboard['snapshot']['top_count']);
        $this->assertGreaterThanOrEqual(TeacherDashboardService::TOP_AT_LEAST, $dashboard['snapshot']['top_best']['average']);
    }

    public function test_an_empty_roster_produces_an_empty_but_valid_dashboard(): void
    {
        $dashboard = $this->build(['teacher' => ['name' => 'T'], 'terms' => ['Term 1', 'Term 2'], 'classes' => []]);

        $this->assertSame(0, $dashboard['snapshot']['students']);
        $this->assertSame([], $dashboard['classes']);
        $this->assertSame([], $dashboard['review']);
    }
}
