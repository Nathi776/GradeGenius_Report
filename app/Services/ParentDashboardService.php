<?php

namespace App\Services;

/**
 * Parent home dashboard for one child.
 *
 * Built from ParentReportService::build() output so a parent sees exactly the figures in the detailed
 * report. It only selects and arranges: how many subjects need support, what is going well, the
 * progress line and recent activity. Nothing here measures anything the report does not.
 */
final class ParentDashboardService
{
    private const LIST_LIMIT = 3;

    /**
     * @param  array<string, mixed>  $report  Output of ParentReportService::build()
     * @param  array<string, string>  $children  Child key => display name
     * @param  array<string, array<string, mixed>>  $childCards  Report-backed child switcher summaries
     * @return array<string, mixed>
     */
    public function build(array $report, array $children, string $activeChild, array $childCards = []): array
    {
        $overall = $report['overall'];
        $subjects = $report['subjects'];
        $goal = $subjects === [] ? null : (int) round(array_sum(array_column($subjects, 'target')) / count($subjects));

        return [
            'has_marks' => true,
            'children' => $children,
            'child_cards' => $childCards,
            'active_child' => $activeChild,
            'child' => ['name' => $report['student']['name'], 'first_name' => $report['first_name'], 'grade' => $report['student']['grade']],
            'summary' => $report['summary'],
            'next_step' => $report['next_step'],
            'snapshot' => [
                'average' => $overall['current'],
                'level' => $overall['level'],
                'change' => $overall['change'],
                'has_comparison' => $overall['has_comparison'],
                'term' => $overall['current_term'],
                'first_term' => $overall['first_term'],
                'at_target' => $overall['at_target'],
                'subject_count' => $overall['subject_count'],
                'goal' => $goal,
            ],
            'progress' => [
                'labels' => $report['terms'],
                'series' => [$report['chart']['series'][0]],
            ],
            'subjects' => $report['subjects'],
            'support' => array_slice($report['needs_support'], 0, self::LIST_LIMIT),
            'support_total' => count($report['needs_support']),
            'wins' => array_slice($report['going_well'], 0, self::LIST_LIMIT),
            'activity' => $report['activity'],
            'recent' => array_slice($report['assessments'], 0, 4),
            'activity_groups' => $this->activityGroups(array_slice($report['assessments'], 0, 6)),
        ];
    }

    /**
     * Dashboard for a linked child whose teacher has not recorded any marks yet. Nothing is invented:
     * the page shows who the child is and says plainly that there is nothing to report.
     *
     * @param  array<string, string>  $children  Child key => display name
     * @param  array<string, array<string, mixed>>  $childCards
     * @return array<string, mixed>
     */
    public function buildWithoutMarks(array $children, string $activeChild, array $childCards): array
    {
        $name = $children[$activeChild];

        return [
            'has_marks' => false,
            'children' => $children,
            'child_cards' => $childCards,
            'active_child' => $activeChild,
            'child' => ['name' => $name, 'first_name' => explode(' ', trim($name))[0], 'grade' => null],
        ];
    }

    /**
     * Group recorded quiz and past-paper results by their available completion date.
     *
     * @param  list<array<string, mixed>>  $assessments
     * @return list<array{date: string, date_label: string, actions: list<array<string, mixed>>}>
     */
    private function activityGroups(array $assessments): array
    {
        $groups = [];
        foreach ($assessments as $assessment) {
            $date = $assessment['date'];
            if (! isset($groups[$date])) {
                $groups[$date] = [
                    'date' => $date,
                    'date_label' => date('D · j M Y', strtotime($date.' UTC')),
                    'actions' => [],
                ];
            }
            $groups[$date]['actions'][] = $assessment;
        }

        return array_values($groups);
    }
}
