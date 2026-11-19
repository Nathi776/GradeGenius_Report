<?php

namespace Tests\Unit;

use App\Services\ParentDashboardService;
use App\Services\ParentReportService;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class ParentDashboardServiceTest extends TestCase
{
    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function build(string $child = 'nathi'): array
    {
        $linked = DemoReportData::childrenForParent();
        $names = array_map(fn (array $raw): string => $raw['student']['name'], $linked);
        $report = (new ParentReportService(new StudentReportService))->build($linked[$child], '2026-11-15');

        return [$report, (new ParentDashboardService)->build($report, $names, $child)];
    }

    public function test_snapshot_reuses_the_report_figures(): void
    {
        [$report, $dashboard] = $this->build();
        $o = $report['overall'];

        $this->assertSame($o['current'], $dashboard['snapshot']['average']);
        $this->assertSame($o['change'], $dashboard['snapshot']['change']);
        $this->assertSame($o['at_target'], $dashboard['snapshot']['at_target']);
        $this->assertSame($report['summary'], $dashboard['summary']);
        $this->assertSame($report['terms'], $dashboard['progress']['labels']);
        $this->assertSame($report['activity'], $dashboard['activity']);
    }

    public function test_support_lists_the_report_order_and_is_limited_to_three(): void
    {
        [$report, $dashboard] = $this->build();

        $this->assertLessThanOrEqual(3, count($dashboard['support']));
        $this->assertSame(array_slice(array_column($report['needs_support'], 'key'), 0, 3), array_column($dashboard['support'], 'key'));
        $this->assertSame(count($report['needs_support']), $dashboard['support_total']);
    }

    public function test_dashboard_exposes_every_report_subject_for_the_parent_view(): void
    {
        [$report, $dashboard] = $this->build();

        $this->assertSame(array_column($report['subjects'], 'key'), array_column($dashboard['subjects'], 'key'));
    }

    public function test_activity_timeline_groups_recent_assessments_by_recorded_date(): void
    {
        [$report, $dashboard] = $this->build();
        $timeline = $dashboard['activity_groups'];
        $flattened = [];

        foreach ($timeline as $group) {
            $this->assertSame($group['date'], $group['actions'][0]['date']);
            foreach ($group['actions'] as $action) {
                $this->assertSame($group['date'], $action['date']);
                $flattened[] = $action;
            }
        }

        $this->assertSame(array_slice($report['assessments'], 0, 6), $flattened);
    }

    public function test_children_and_active_child_are_passed_through(): void
    {
        [, $dashboard] = $this->build('sipho');

        $this->assertSame('sipho', $dashboard['active_child']);
        $this->assertSame('Sipho', $dashboard['child']['first_name']);
        $this->assertSame(['nathi', 'sipho'], array_keys($dashboard['children']));
    }

    public function test_goal_is_the_mean_of_the_subject_targets(): void
    {
        [$report, $dashboard] = $this->build();

        $this->assertSame((int) round(array_sum(array_column($report['subjects'], 'target')) / count($report['subjects'])), $dashboard['snapshot']['goal']);
    }
}
