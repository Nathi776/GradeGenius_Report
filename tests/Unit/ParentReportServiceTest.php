<?php

namespace Tests\Unit;

use App\Services\ParentReportService;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class ParentReportServiceTest extends TestCase
{
    private const TODAY = '2026-10-08';

    private function report(?array $raw = null): array
    {
        return (new ParentReportService(new StudentReportService))
            ->build($raw ?? DemoReportData::forStudent(self::TODAY), self::TODAY);
    }

    public function test_parent_sees_exactly_the_same_numbers_as_the_learner(): void
    {
        $raw = DemoReportData::forStudent(self::TODAY);
        $student = (new StudentReportService)->build($raw);
        $parent = $this->report($raw);

        $this->assertSame($student['overall'], $parent['overall']);
        $this->assertSame($student['chart'], $parent['chart']);
        $this->assertSame($student['assessments'], $parent['assessments']);
        $this->assertSame(array_column($student['subjects'], 'current'), array_column($parent['subjects'], 'current'));
        $this->assertSame(array_column($student['subjects'], 'name'), array_column($parent['subjects'], 'name'));
    }

    public function test_plain_language_summary_and_next_step(): void
    {
        $r = $this->report();
        $this->assertSame('Nathi is averaging 74%, up 11 points since Term 1. 4 of 5 subjects are at goal.', $r['summary']);
        $this->assertStringContainsString('Reconciliations', $r['next_step']);
        $this->assertStringContainsString('Accounting', $r['next_step']);
    }

    public function test_needs_support_and_going_well_never_overlap_and_cover_every_subject(): void
    {
        $r = $this->report();
        $support = array_column($r['needs_support'], 'name');
        $well = array_column($r['going_well'], 'name');

        $this->assertSame(['Accounting'], $support);
        $this->assertSame(['Mathematics', 'Life Sciences', 'Physical Sciences', 'English Home Language'], $well);
        $this->assertSame([], array_intersect($support, $well));
        $this->assertEqualsCanonicalizing(array_column($r['subjects'], 'name'), array_merge($support, $well));
    }

    public function test_subject_below_goal_but_improving_is_listed_once_as_needing_support(): void
    {
        $raw = DemoReportData::forStudent(self::TODAY);
        $raw['subjects'][4]['term_averages'] = [50, 55, 60, 66];   // +16 but goal is 70
        $r = $this->report($raw);

        $this->assertSame(['Accounting'], array_column($r['needs_support'], 'name'));
        $this->assertNotContains('Accounting', array_column($r['going_well'], 'name'));
        $this->assertSame('close', $r['needs_support'][0]['target_status']['state']);
    }

    public function test_when_every_goal_is_met_the_message_changes(): void
    {
        $raw = DemoReportData::forStudent(self::TODAY);
        $raw['subjects'][4]['term_averages'] = [70, 72, 74, 76];
        $r = $this->report($raw);

        $this->assertSame([], $r['needs_support']);
        $this->assertStringContainsString('meeting every goal', $r['next_step']);
        $this->assertStringContainsString('5 of 5 subjects are at goal', $r['summary']);
    }

    public function test_activity_counts_completed_assessments_from_their_dates(): void
    {
        $raw = DemoReportData::forStudent(self::TODAY);
        $within = 0;
        $before = 0;
        foreach ($raw['subjects'] as $s) {
            foreach (array_merge($s['quizzes'], $s['past_papers']) as $a) {
                $days = intdiv(strtotime(self::TODAY.' UTC') - strtotime($a['date'].' UTC'), 86400);
                $days < 30 ? $within++ : ($days < 60 ? $before++ : null);
            }
        }
        $activity = $this->report($raw)['activity'];

        $this->assertSame($within, $activity['completed_30d']);
        $this->assertSame($before, $activity['completed_prev_30d']);
        $this->assertSame($within - $before, $activity['delta']);
        $this->assertSame(4, $activity['weeks_active']);
        $this->assertSame('7 Oct', $activity['last_label']);
        $this->assertSame('Yesterday', $activity['last_ago']);
    }

    public function test_activity_when_nothing_was_completed_recently(): void
    {
        $activity = $this->report()['activity'];
        $quiet = (new ParentReportService(new StudentReportService))
            ->build(DemoReportData::forStudent(self::TODAY), '2027-06-01')['activity'];

        $this->assertGreaterThan(0, $activity['completed_30d']);
        $this->assertSame(0, $quiet['completed_30d']);
        $this->assertSame(0, $quiet['weeks_active']);
        $this->assertStringContainsString('days ago', $quiet['last_ago']);
    }

    public function test_each_subject_has_status_recent_results_and_home_steps(): void
    {
        foreach ($this->report()['subjects'] as $s) {
            $this->assertContains($s['status_label'], ['On goal', 'Almost at goal', 'Needs support']);
            $this->assertNotEmpty($s['recent'], $s['name'].' has recent results');
            $this->assertNotEmpty($s['home_steps'], $s['name'].' has home steps');
            $this->assertLessThanOrEqual(3, count($s['recent']));
            foreach ($s['recent'] as $a) {
                $this->assertSame($s['key'], $a['subject_key']);
            }
        }
    }

    public function test_each_child_gets_their_own_report_with_their_own_subject_count(): void
    {
        $kids = DemoReportData::childrenForParent(self::TODAY);
        $this->assertSame(['nathi', 'sipho'], array_keys($kids));

        $nathi = $this->report($kids['nathi']);
        $sipho = $this->report($kids['sipho']);
        $this->assertCount(5, $nathi['subjects']);
        $this->assertCount(3, $sipho['subjects']);
        $this->assertSame('Sipho', $sipho['first_name']);
        $this->assertStringStartsWith('Sipho is averaging', $sipho['summary']);
        $this->assertSame(3, $sipho['overall']['subject_count']);
    }

    public function test_plural_wording_for_a_single_subject(): void
    {
        $raw = DemoReportData::forStudent(self::TODAY);
        $raw['subjects'] = [$raw['subjects'][0]];
        $this->assertStringContainsString('1 of 1 subject is at goal', $this->report($raw)['summary']);
    }
}
