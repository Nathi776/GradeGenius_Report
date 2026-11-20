<?php

namespace Tests\Unit;

use App\Services\StudyPlannerService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class StudyPlannerServiceTest extends TestCase
{
    private function items(int $count, string $subject = 'mathematics'): array
    {
        return array_map(fn (int $i): array => ['subject_key' => $subject, 'topic' => "Topic $i"], range(1, $count));
    }

    public function test_highest_ranked_topic_is_planned_first_starting_today(): void
    {
        $today = CarbonImmutable::parse('2026-11-20');

        $plan = (new StudyPlannerService)->build($this->items(3), [], $today);

        $this->assertSame('Topic 1', $plan[0]['topic']);
        $this->assertSame('2026-11-20', $plan[0]['planned_date']);
        $this->assertSame(30, $plan[0]['minutes']);
    }

    public function test_a_day_never_exceeds_the_daily_limit(): void
    {
        $subjects = ['mathematics', 'physical-sciences', 'english-home-language'];
        $items = [];
        foreach (range(0, 11) as $i) {
            $items[] = ['subject_key' => $subjects[$i % 3], 'topic' => "Topic $i"];
        }

        $plan = (new StudyPlannerService)->build($items, [], CarbonImmutable::parse('2026-11-20'));

        $this->assertCount(12, $plan);
        foreach (array_count_values(array_column($plan, 'planned_date')) as $count) {
            $this->assertLessThanOrEqual(3, $count);
        }
    }

    public function test_no_more_than_two_topics_of_one_subject_a_day(): void
    {
        $plan = (new StudyPlannerService)->build($this->items(5), [], CarbonImmutable::parse('2026-11-20'));

        foreach (array_count_values(array_column($plan, 'planned_date')) as $count) {
            $this->assertLessThanOrEqual(2, $count);
        }
    }

    public function test_topics_are_planned_before_the_exam_of_their_subject(): void
    {
        $exams = [['subject_key' => 'mathematics', 'date' => '2026-11-22']];

        $plan = (new StudyPlannerService)->build($this->items(6), $exams, CarbonImmutable::parse('2026-11-20'));

        foreach ($plan as $row) {
            $this->assertLessThan('2026-11-22', $row['planned_date']);
        }
    }

    public function test_exam_tomorrow_still_allows_a_session_today(): void
    {
        $exams = [['subject_key' => 'mathematics', 'date' => '2026-11-21']];

        $plan = (new StudyPlannerService)->build($this->items(2), $exams, CarbonImmutable::parse('2026-11-20'));

        $this->assertCount(2, $plan);
        $this->assertSame('2026-11-20', $plan[0]['planned_date']);
    }

    public function test_empty_input_gives_an_empty_plan(): void
    {
        $this->assertSame([], (new StudyPlannerService)->build([]));
    }
}
