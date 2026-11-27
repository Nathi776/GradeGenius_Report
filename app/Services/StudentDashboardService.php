<?php

namespace App\Services;

/**
 * Builds the student home dashboard view model from an already-built student report.
 *
 * It never recalculates a figure: averages, targets, topics and assessments all come from
 * StudentReportService::build(), so the home page, the Academic Performance page and any future page
 * agree. Plain arrays in, plain arrays out; no framework dependency, easy to unit test.
 *
 * Only what the report data can support is produced here. Exams, study tasks and activity types other
 * than quizzes and past papers need new data sources, so the view shows empty states for them.
 */
final class StudentDashboardService
{
    /** Topics below this mastery are "High" priority; the rest of the report's attention topics are "Medium". */
    private const HIGH_PRIORITY_BELOW = 55;

    private const MAX_ACTIONS = 3;

    private const MAX_ACTIVITY = 4;

    /**
     * @param  array<string, mixed>  $report  Output of StudentReportService::build()
     * @return array{
     *     student: array<string, mixed>,
     *     status: array<string, string>,
     *     snapshot: array<string, int|string|null>,
     *     focus: array<string, mixed>|null,
     *     actions: list<array<string, mixed>>,
     *     subjects: list<array<string, mixed>>,
     *     activity: list<array<string, mixed>>
     * }
     */
    public function build(array $report): array
    {
        return [
            'student' => $report['student'],
            'terms' => $report['terms'],
            'selected_term' => $report['selected_term'],
            'selected_term_index' => $report['selected_term_index'],
            'all_terms_selected' => $report['all_terms_selected'],
            'trend' => [
                'labels' => $report['terms'],
                'series' => [$report['chart']['series'][0]],
            ],
            'status' => $report['overall']['target_status'],
            'snapshot' => $this->snapshot($report),
            'assessment_snapshot' => $this->assessmentSnapshot($report),
            'focus' => $this->focus($report),
            'actions' => $this->learningActions($report['subjects']),
            'subjects' => $this->subjectOverview($report['subjects']),
            'activity' => array_slice($report['assessments'], 0, self::MAX_ACTIVITY),
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, int|null>
     */
    private function assessmentSnapshot(array $report): array
    {
        $quizzes = array_column(array_filter($report['assessments'], fn (array $assessment): bool => $assessment['type'] === 'quiz'), 'score');
        $papers = array_column(array_filter($report['assessments'], fn (array $assessment): bool => $assessment['type'] === 'past_paper'), 'score');

        return [
            'quizzes_completed' => count($quizzes),
            'quiz_average' => $quizzes ? (int) round(array_sum($quizzes) / count($quizzes)) : null,
            'past_papers_completed' => count($papers),
            'past_paper_average' => $papers ? (int) round(array_sum($papers) / count($papers)) : null,
            'average_score' => $report['assessment_stats']['average_score'],
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, int|string|null>
     */
    private function snapshot(array $report): array
    {
        $overall = $report['overall'];
        $stats = $report['assessment_stats'];

        return [
            'average' => $overall['current'],
            'change' => $overall['change'],
            'first_term' => $overall['first_term'],
            'at_target' => $overall['at_target'],
            'subject_count' => $overall['subject_count'],
            'below_goal' => $overall['subject_count'] - $overall['at_target'],
            'questions_answered' => $stats['questions_answered'],
            'accuracy' => $stats['quiz_accuracy'],
        ];
    }

    /**
     * The subject furthest below its own target, with its weakest topic to start on.
     *
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>|null
     */
    private function focus(array $report): ?array
    {
        foreach ($report['subjects'] as $subject) {
            if ($subject['key'] !== $report['focus_key']) {
                continue;
            }

            return [
                'key' => $subject['key'],
                'name' => $subject['name'],
                'color' => $subject['color'],
                'current' => $subject['current'],
                'target' => $subject['target'],
                'status' => $subject['target_status'],
                'weakest_topic' => $subject['attention'][0] ?? null,
            ];
        }

        return null;
    }

    /**
     * Lowest-mastery topics across every subject, each tied to the evidence that put it here.
     *
     * @param  list<array<string, mixed>>  $subjects
     * @return list<array<string, mixed>>
     */
    private function learningActions(array $subjects): array
    {
        $candidates = [];
        foreach ($subjects as $subject) {
            foreach ($subject['attention'] as $topic) {
                $candidates[] = [
                    'subject' => $subject['name'],
                    'subject_key' => $subject['key'],
                    'color' => $subject['color'],
                    'topic' => $topic['name'],
                    'mastery' => $topic['mastery'],
                ];
            }
        }

        usort($candidates, fn (array $a, array $b): int => [$a['mastery'], $a['subject'], $a['topic']] <=> [$b['mastery'], $b['subject'], $b['topic']]);

        return array_map(function (array $candidate): array {
            $isHigh = $candidate['mastery'] < self::HIGH_PRIORITY_BELOW;

            return $candidate + [
                'priority' => $isHigh ? 'High' : 'Medium',
                'tone' => $isHigh ? 'low' : 'mid',
            ];
        }, array_slice($candidates, 0, self::MAX_ACTIONS));
    }

    /**
     * @param  list<array<string, mixed>>  $subjects
     * @return list<array<string, mixed>>
     */
    private function subjectOverview(array $subjects): array
    {
        return array_map(fn (array $subject): array => [
            'key' => $subject['key'],
            'name' => $subject['name'],
            'color' => $subject['color'],
            'current' => $subject['current'],
            'target' => $subject['target'],
            'tone' => $subject['target_status']['tone'],
            'status_label' => $subject['target_status']['label'],
            'quiz_count' => $subject['quiz_stats']['completed'],
            'quiz_average' => $subject['quiz_stats']['average'],
            'past_paper_count' => count($subject['past_papers']),
            'past_paper_average' => $subject['past_paper_average'],
            'weakest_topic' => $subject['attention'][0] ?? null,
        ], $subjects);
    }
}
