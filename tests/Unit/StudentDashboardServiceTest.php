<?php

namespace Tests\Unit;

use App\Services\StudentDashboardService;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class StudentDashboardServiceTest extends TestCase
{
    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function build(?array $raw = null): array
    {
        $report = (new StudentReportService)->build($raw ?? DemoReportData::forStudent());

        return [$report, (new StudentDashboardService)->build($report)];
    }

    /**
     * @return array<string, mixed>
     */
    private function everyGoalMetData(): array
    {
        $raw = DemoReportData::forStudent();
        foreach ($raw['subjects'] as &$subject) {
            $subject['term_averages'][array_key_last($subject['term_averages'])] = 100;
            $subject['topics'] = array_map(fn (int $mastery): int => 90, $subject['topics']);
        }
        unset($subject);

        return $raw;
    }

    public function test_snapshot_reuses_the_report_figures_without_recalculating(): void
    {
        [$report, $dashboard] = $this->build();
        $snapshot = $dashboard['snapshot'];

        $this->assertSame($report['overall']['current'], $snapshot['average']);
        $this->assertSame($report['overall']['change'], $snapshot['change']);
        $this->assertSame($report['overall']['subject_count'], $snapshot['subject_count']);
        $this->assertSame($report['overall']['at_target'], $snapshot['at_target']);
        $this->assertSame($snapshot['subject_count'] - $snapshot['at_target'], $snapshot['below_goal']);
        $this->assertSame(array_sum(array_column(array_column($report['subjects'], 'questions'), 'attempted')), $snapshot['questions_answered']);
        $this->assertSame($report['assessment_stats']['quiz_accuracy'], $snapshot['accuracy']);
    }

    public function test_focus_is_the_subject_furthest_below_its_own_target(): void
    {
        [$report, $dashboard] = $this->build();
        $focus = $dashboard['focus'];

        $gaps = [];
        foreach ($report['subjects'] as $subject) {
            $gaps[$subject['key']] = $subject['target'] - $subject['current'];
        }

        $this->assertSame(array_search(max($gaps), $gaps, true), $focus['key']);
        $this->assertNotSame('met', $focus['status']['state']);
        $this->assertNotNull($focus['weakest_topic']);
        $this->assertLessThan(65, $focus['weakest_topic']['mastery']);
    }

    public function test_learning_actions_are_the_lowest_topics_across_all_subjects(): void
    {
        [$report, $dashboard] = $this->build();
        $actions = $dashboard['actions'];

        $this->assertNotEmpty($actions);
        $this->assertLessThanOrEqual(3, count($actions));

        $masteries = array_column($actions, 'mastery');
        $sorted = $masteries;
        sort($sorted);
        $this->assertSame($sorted, $masteries, 'lowest mastery first');

        $subjectKeys = array_column($report['subjects'], 'key');
        foreach ($actions as $action) {
            $this->assertContains($action['subject_key'], $subjectKeys);
            $this->assertLessThan(65, $action['mastery']);
            $this->assertSame($action['mastery'] < 55 ? 'High' : 'Medium', $action['priority']);
            $this->assertSame($action['mastery'] < 55 ? 'low' : 'mid', $action['tone']);
        }

        $lowestAnywhere = min(array_map(
            fn (array $subject): int => min(array_column($subject['topics'], 'mastery')),
            $report['subjects'],
        ));
        $this->assertSame($lowestAnywhere, $actions[0]['mastery']);
    }

    public function test_every_subject_appears_in_the_overview_with_its_own_status(): void
    {
        [$report, $dashboard] = $this->build();

        $this->assertSame(array_column($report['subjects'], 'name'), array_column($dashboard['subjects'], 'name'));
        foreach ($dashboard['subjects'] as $i => $subject) {
            $this->assertSame($report['subjects'][$i]['current'], $subject['current']);
            $this->assertSame($report['subjects'][$i]['target_status']['tone'], $subject['tone']);
        }
    }

    public function test_recent_activity_is_the_newest_four_assessments(): void
    {
        [$report, $dashboard] = $this->build();

        $this->assertCount(4, $dashboard['activity']);
        $this->assertSame(array_slice($report['assessments'], 0, 4), $dashboard['activity']);

        $dates = array_column($dashboard['activity'], 'date');
        $newestFirst = $dates;
        rsort($newestFirst);
        $this->assertSame($newestFirst, $dates);
    }

    public function test_nothing_is_invented_when_every_goal_is_met_and_no_topic_is_weak(): void
    {
        [, $dashboard] = $this->build($this->everyGoalMetData());

        $this->assertSame([], $dashboard['actions']);
        $this->assertSame('met', $dashboard['focus']['status']['state']);
        $this->assertSame(0, $dashboard['snapshot']['below_goal']);
    }
}
