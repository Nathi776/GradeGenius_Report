<?php

namespace App\Services;

/**
 * Teacher home dashboard: class-wide figures built from assigned classes and their learners' term
 * averages (plain arrays in, plain arrays out).
 *
 * "At risk" is never a prediction. A learner is flagged for review only when an explicit, visible
 * rule is met (see RISK_BELOW and DECLINE_POINTS), and the reason is returned with the flag so the
 * teacher can see the evidence. A flag means "look at this learner", not a judgement of ability.
 */
final class TeacherDashboardService
{
    public const RISK_BELOW = 50;

    public const DECLINE_POINTS = 5;

    public const TOP_AT_LEAST = 80;

    private const NEAR_TARGET = 5;

    /**
     * @param  array<string, mixed>  $data  Shape of DemoReportData::classesForTeacher()
     * @return array<string, mixed>
     */
    public function build(array $data): array
    {
        $terms = $data['terms'];
        $classes = [];
        $learners = [];
        $termTotals = array_fill(0, count($terms), 0);
        $termCounts = array_fill(0, count($terms), 0);

        foreach ($data['classes'] as $class) {
            $latest = [];
            $atRisk = 0;
            foreach ($class['learners'] as $learner) {
                $current = (int) end($learner['term_averages']);
                $latest[] = $current;
                foreach ($learner['term_averages'] as $i => $mark) {
                    $termTotals[$i] += $mark;
                    $termCounts[$i]++;
                }
                $reasons = $this->riskReasons($learner['term_averages']);
                $atRisk += $reasons === [] ? 0 : 1;
                $learners[$learner['id']] = [
                    'id' => $learner['id'],
                    'name' => $learner['name'],
                    'class' => $class['subject'].' · '.$class['name'],
                    'average' => $current,
                    'change' => $current - $learner['term_averages'][0],
                    'reasons' => $reasons,
                ];
            }
            $average = $this->mean($latest);
            $classes[] = [
                'key' => $class['key'],
                'name' => $class['name'],
                'subject' => $class['subject'],
                'grade' => $class['grade'],
                'learner_count' => count($class['learners']),
                'average' => $average,
                'target' => $class['target'],
                'tone' => $this->tone($average, $class['target']),
                'attention' => $atRisk,
            ];
        }

        $trend = [];
        foreach ($termTotals as $i => $total) {
            $trend[] = $termCounts[$i] === 0 ? null : (int) round($total / $termCounts[$i]);
        }

        $atRiskList = array_values(array_filter($learners, fn (array $l): bool => $l['reasons'] !== []));
        usort($atRiskList, fn (array $a, array $b): int => [$a['average'], $a['name']] <=> [$b['average'], $b['name']]);
        $top = array_values(array_filter($learners, fn (array $l): bool => $l['average'] >= self::TOP_AT_LEAST));
        usort($top, fn (array $a, array $b): int => [$b['average'], $a['name']] <=> [$a['average'], $b['name']]);

        $currentAverage = $this->mean(array_column($learners, 'average'));
        $firstAverage = $trend[0] ?? $currentAverage;

        return [
            'teacher' => $data['teacher'],
            'terms' => $terms,
            'snapshot' => [
                'students' => count($learners),
                'class_count' => count($classes),
                'average' => $currentAverage,
                'change' => $currentAverage - $firstAverage,
                'first_term' => $terms[0] ?? '',
                'at_risk' => count($atRiskList),
                'top_count' => count($top),
                'top_best' => $top[0] ?? null,
            ],
            'criteria' => [
                'risk_below' => self::RISK_BELOW,
                'decline_points' => self::DECLINE_POINTS,
                'top_at_least' => self::TOP_AT_LEAST,
            ],
            'trend' => ['labels' => $terms, 'points' => $trend],
            'classes' => $classes,
            'review' => array_slice($atRiskList, 0, 5),
            'insights' => $this->insights($classes, count($atRiskList), $currentAverage - $firstAverage, $firstAverage === $currentAverage ? '' : $terms[0] ?? ''),
        ];
    }

    /**
     * @param  array<int, int>  $termAverages
     * @return array<int, string>
     */
    private function riskReasons(array $termAverages): array
    {
        $reasons = [];
        $current = (int) end($termAverages);
        if ($current < self::RISK_BELOW) {
            $reasons[] = 'Average '.$current.'% is below '.self::RISK_BELOW.'%';
        }
        if (count($termAverages) >= 2) {
            $drop = (int) $termAverages[count($termAverages) - 2] - $current;
            if ($drop >= self::DECLINE_POINTS) {
                $reasons[] = 'Down '.$drop.' points since last term';
            }
        }

        return $reasons;
    }

    /**
     * @param  array<int, array<string, mixed>>  $classes
     * @return array<int, array{tone: string, text: string}>
     */
    private function insights(array $classes, int $atRisk, int $change, string $firstTerm): array
    {
        $insights = [];
        if ($firstTerm !== '') {
            $points = abs($change).' '.(abs($change) === 1 ? 'point' : 'points');
            $insights[] = ['tone' => $change >= 0 ? 'good' : 'low', 'text' => 'Overall average is '.($change >= 0 ? 'up ' : 'down ').$points.' since '.$firstTerm.'.'];
        }
        $insights[] = $atRisk === 0
            ? ['tone' => 'good', 'text' => 'No learner currently meets the review criteria.']
            : ['tone' => 'mid', 'text' => $atRisk.' '.($atRisk === 1 ? 'learner meets' : 'learners meet').' the review criteria.'];

        if ($classes !== []) {
            $byAverage = $classes;
            usort($byAverage, fn (array $a, array $b): int => $b['average'] <=> $a['average']);
            $best = $byAverage[0];
            $insights[] = ['tone' => 'good', 'text' => 'Highest class average: '.$best['subject'].' ('.$best['name'].') at '.$best['average'].'%.'];

            $byGap = $classes;
            usort($byGap, fn (array $a, array $b): int => ($b['target'] - $b['average']) <=> ($a['target'] - $a['average']));
            $furthest = $byGap[0];
            if ($furthest['average'] < $furthest['target']) {
                $insights[] = ['tone' => $furthest['tone'], 'text' => $furthest['subject'].' ('.$furthest['name'].') is '.($furthest['target'] - $furthest['average']).' points below its '.$furthest['target'].'% target.'];
            }
        }

        return $insights;
    }

    private function tone(int $average, int $target): string
    {
        return $average >= $target ? 'good' : ($target - $average <= self::NEAR_TARGET ? 'mid' : 'low');
    }

    /**
     * @param  array<int, int>  $values
     */
    private function mean(array $values): int
    {
        return $values === [] ? 0 : (int) round(array_sum($values) / count($values));
    }
}
