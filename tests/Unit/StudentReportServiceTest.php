<?php

namespace Tests\Unit;

use App\Services\StudentReportService;
use App\Support\DemoReportData;
use PHPUnit\Framework\TestCase;

class StudentReportServiceTest extends TestCase
{
    public function test_every_subject_is_present_in_the_report_sections(): void
    {
        $report = (new StudentReportService)->build(DemoReportData::forStudent());
        $subjectNames = array_column($report['subjects'], 'name');

        $this->assertCount(5, $subjectNames);
        $this->assertSame(5, $report['overall']['subject_count']);
        $this->assertSame(array_merge(['Overall'], $subjectNames), array_column($report['chart']['series'], 'name'));
        $this->assertEqualsCanonicalizing(
            $subjectNames,
            array_values(array_unique(array_column($report['assessments'], 'subject')))
        );
    }

    public function test_headline_metrics_are_derived_from_subjects(): void
    {
        $report = (new StudentReportService)->build(DemoReportData::forStudent());
        $currentMarks = array_column($report['subjects'], 'current');

        $this->assertSame((int) round(array_sum($currentMarks) / count($currentMarks)), $report['overall']['current']);
        $this->assertSame($report['overall']['current'], end($report['chart']['series'][0]['points']));
        $this->assertSame(74, $report['overall']['current']);
        $this->assertSame(11, $report['overall']['change']);
    }

    public function test_assessment_stats_are_aggregated_across_subjects(): void
    {
        $stats = (new StudentReportService)->build(DemoReportData::forStudent())['assessment_stats'];

        $this->assertSame(10, $stats['quizzes_completed']);
        $this->assertSame(10, $stats['past_papers']);
        $this->assertSame(550, $stats['questions_answered']);
    }
}
