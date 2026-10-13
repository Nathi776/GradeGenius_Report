<?php

namespace Tests\Unit;

use App\Services\StudentReportService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class StudentReportServiceTest extends TestCase
{
    private function report(?array $raw = null): array
    {
        return (new StudentReportService)->build($raw ?? DemoReportData::forStudent());
    }

    public function test_every_subject_is_present_in_every_section(): void
    {
        $r = $this->report();
        $names = array_column($r['subjects'], 'name');

        $this->assertCount(5, $names);
        $this->assertSame($r['overall']['subject_count'], count($names));
        // chart: overall + one line per subject
        $this->assertSame(array_merge(['Overall'], $names), array_column($r['chart']['series'], 'name'));
        // assessment table covers every subject
        $this->assertEqualsCanonicalizing($names, array_values(array_unique(array_column($r['assessments'], 'subject'))));
        foreach ($r['subjects'] as $s) {
            $this->assertNotEmpty($s['topics'], $s['name'].' has topics');
            $this->assertNotEmpty($s['past_papers'], $s['name'].' has past papers');
            $this->assertNotEmpty($s['quizzes'], $s['name'].' has quizzes');
        }
    }

    public function test_works_for_any_number_of_subjects(): void
    {
        $raw = DemoReportData::forStudent();
        foreach ([1, 3, 7] as $n) {
            $subjects = [];
            for ($i = 0; $i < $n; $i++) {
                $subjects[] = ['name' => "Subject $i"] + $raw['subjects'][$i % 5];
            }
            $r = $this->report(['subjects' => $subjects] + $raw);
            $this->assertCount($n, $r['subjects']);
            $this->assertSame($n, $r['overall']['subject_count']);
            $this->assertCount($n + 1, $r['chart']['series']);
        }
    }

    public function test_headline_numbers_are_derived_from_subjects(): void
    {
        $r = $this->report();
        $currents = array_column($r['subjects'], 'current');
        $this->assertSame((int) round(array_sum($currents) / count($currents)), $r['overall']['current']);
        // the chart's last point is the headline average (no more line/dot disagreement)
        $overallSeries = $r['chart']['series'][0]['points'];
        $this->assertSame($r['overall']['current'], end($overallSeries));
        $this->assertSame(74, $r['overall']['current']);
        $this->assertSame(11, $r['overall']['change']);
    }

    public function test_subject_stats_match_their_inputs(): void
    {
        $maths = $this->report()['subjects'][0];
        $this->assertSame(78, $maths['current']);
        $this->assertSame(16, $maths['change']);
        $this->assertSame(75, $maths['past_paper_average']);   // 78, 71, 84, 68
        $this->assertSame(112, $maths['questions']['correct']);
        $this->assertSame(38, $maths['questions']['incorrect']);
        $this->assertSame(75, $maths['questions']['accuracy']);
        $this->assertSame(10, $maths['quiz_stats']['completed']);
        $this->assertSame(90, $maths['quiz_stats']['highest']);
        $this->assertSame(52, $maths['quiz_stats']['lowest']);
        $this->assertSame(['Algebra', 'Functions', 'Calculus'], array_column($maths['strongest'], 'name'));
        $this->assertSame(['Probability', 'Geometry', 'Trigonometry'], array_column($maths['attention'], 'name'));
    }

    public function test_assessment_stats_use_one_definition_each(): void
    {
        $s = $this->report()['assessment_stats'];
        $this->assertSame(37, $s['quizzes_completed']);
        $this->assertSame(15, $s['past_papers']);
        $this->assertSame(550, $s['questions_answered']);
        $this->assertSame((int) round((112 + 84 + 68 + 88 + 46) / 550 * 100), $s['quiz_accuracy']);
    }

    public function test_assessments_are_newest_first(): void
    {
        $dates = array_column($this->report()['assessments'], 'date');
        $sorted = $dates;
        rsort($sorted);
        $this->assertSame($sorted, $dates);
    }

    public function test_target_status_and_default_focus(): void
    {
        $r = $this->report();
        $bySubject = array_column($r['subjects'], null, 'name');
        $this->assertSame('met', $bySubject['Mathematics']['target_status']['state']);
        $this->assertSame('behind', $bySubject['Accounting']['target_status']['state']);
        $this->assertSame('8 pts to go', $bySubject['Accounting']['target_status']['detail']);
        $this->assertSame('accounting', $r['focus_key']);   // furthest below target opens first
        $this->assertSame('close', $r['overall']['target_status']['state']);
        $this->assertSame('1 pt to go', $r['overall']['target_status']['detail']);
    }

    public function test_achievement_levels_follow_the_caps_scale(): void
    {
        $r = $this->report();
        $levels = array_column($r['subjects'], 'level', 'name');
        $this->assertSame(
            ['Mathematics' => 6, 'Physical Sciences' => 6, 'English Home Language' => 6, 'Life Sciences' => 7, 'Accounting' => 5],
            $levels
        );
        $this->assertSame(6, $r['overall']['level']);
        foreach ([[100, 7], [80, 7], [79.9, 6], [70, 6], [60, 5], [50, 4], [40, 3], [30, 2], [29, 1], [0, 1]] as [$score, $level]) {
            $this->assertSame($level, StudentReportService::level($score), "score $score");
        }
    }

    public function test_subject_keys_are_unique_slugs(): void
    {
        $keys = array_column($this->report()['subjects'], 'key');
        $this->assertSame(array_unique($keys), $keys);
        $this->assertContains('english-home-language', $keys);
    }
}
