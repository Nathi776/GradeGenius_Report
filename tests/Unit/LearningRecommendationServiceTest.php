<?php

namespace Tests\Unit;

use App\Services\LearningRecommendationService;
use App\Services\StudentReportService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class LearningRecommendationServiceTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function report(): array
    {
        $raw = DemoReportData::forStudent();

        return (new StudentReportService)->build($raw, count($raw['terms']) - 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function exam(string $subjectKey, int $days): array
    {
        return [
            'id' => 1, 'subject_key' => $subjectKey, 'subject' => $subjectKey, 'title' => 'Test', 'paper' => 'Paper 1',
            'date' => '2026-12-01', 'date_label' => 'Tue 1 Dec', 'days_until' => $days, 'countdown' => 'In '.$days.' days',
        ];
    }

    public function test_every_topic_below_the_threshold_is_listed_and_none_above_it(): void
    {
        $report = $this->report();
        $plan = (new LearningRecommendationService)->build($report);

        $expected = 0;
        foreach ($report['subjects'] as $subject) {
            $expected += count(array_filter($subject['topics'], fn (array $t): bool => $t['mastery'] < LearningRecommendationService::WEAK_BELOW));
        }

        $this->assertSame($expected, count($plan['items']));
        $this->assertSame($expected, $plan['summary']['weak_topics']);
        foreach ($plan['items'] as $item) {
            $this->assertLessThan(LearningRecommendationService::WEAK_BELOW, $item['mastery']);
        }
    }

    public function test_without_exams_the_lowest_mastery_comes_first(): void
    {
        $plan = (new LearningRecommendationService)->build($this->report());

        $masteries = array_column($plan['items'], 'mastery');
        $sorted = $masteries;
        sort($sorted);
        $this->assertSame($sorted, $masteries);
    }

    public function test_every_item_carries_its_reasons_and_priority_rule(): void
    {
        $plan = (new LearningRecommendationService)->build($this->report());

        $this->assertNotEmpty($plan['items']);
        foreach ($plan['items'] as $item) {
            $this->assertNotEmpty($item['reasons']);
            $this->assertStringContainsString($item['mastery'].'%', $item['reasons'][0]);
            $this->assertSame($item['mastery'] < LearningRecommendationService::HIGH_BELOW ? 'High' : 'Medium', $item['priority']);
        }
    }

    public function test_a_close_exam_moves_its_subjects_topics_up_and_makes_them_high_priority(): void
    {
        $report = $this->report();
        $without = (new LearningRecommendationService)->build($report);
        $mathBefore = array_values(array_filter($without['items'], fn (array $i): bool => $i['subject_key'] === 'mathematics'));
        $this->assertNotEmpty($mathBefore);

        $with = (new LearningRecommendationService)->build($report, [$this->exam('mathematics', 5)]);
        $mathAfter = array_values(array_filter($with['items'], fn (array $i): bool => $i['subject_key'] === 'mathematics'));

        $this->assertSame(count($mathBefore), count($mathAfter));
        foreach ($mathAfter as $item) {
            $this->assertSame('High', $item['priority']);
            $this->assertStringContainsString('Tue 1 Dec', implode(' ', $item['reasons']));
        }
        $this->assertSame(1, $with['summary']['exams_soon']);

        $positionBefore = array_search($mathBefore[0]['topic'], array_column($without['items'], 'topic'), true);
        $positionAfter = array_search($mathAfter[0]['topic'], array_column($with['items'], 'topic'), true);
        $this->assertLessThanOrEqual($positionBefore, $positionAfter);
    }

    public function test_a_far_away_exam_does_not_change_priority(): void
    {
        $report = $this->report();
        $without = (new LearningRecommendationService)->build($report);
        $with = (new LearningRecommendationService)->build($report, [$this->exam('mathematics', 90)]);

        $this->assertSame(array_column($without['items'], 'priority'), array_column($with['items'], 'priority'));
        $this->assertSame(array_column($without['items'], 'topic'), array_column($with['items'], 'topic'));
    }

    public function test_nothing_is_invented_when_every_topic_is_strong(): void
    {
        $raw = DemoReportData::forStudent();
        foreach ($raw['subjects'] as &$subject) {
            $subject['topics'] = array_map(fn (int $mastery): int => 90, $subject['topics']);
        }
        unset($subject);
        $report = (new StudentReportService)->build($raw, count($raw['terms']) - 1);

        $plan = (new LearningRecommendationService)->build($report, [$this->exam('mathematics', 3)]);

        $this->assertSame([], $plan['items']);
        $this->assertSame(0, $plan['summary']['weak_topics']);
    }
}
