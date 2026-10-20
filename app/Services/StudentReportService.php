<?php

namespace App\Services;

/**
 * Builds the student report view model from raw per-subject data.
 *
 * One source of truth: every figure on the page (tiles, chart, subject cards, assessment table) is
 * derived here from the same subject list, so the numbers always agree and every subject appears
 * everywhere. Plain arrays in, plain arrays out; no framework dependency, easy to unit test.
 *
 * Raw input, per subject:
 *   name, term_averages[] (one per term), target?, questions{attempted,correct},
 *   topics{name => mastery}, past_papers[{title,score,date}], quizzes[{title,score,attempts,date}]
 */
final class StudentReportService
{
    /** Distinct on a dark background; wraps if a learner has more subjects than colours. */
    private const PALETTE = ['#8ee8f4', '#f23d99', '#4ade80', '#c084fc', '#20a9bb', '#ff69bd', '#38bdf8'];

    public function build(array $raw): array
    {
        $terms = $raw['terms'];
        $defaultTarget = (int) ($raw['target'] ?? 75);

        $subjects = [];
        foreach (array_values($raw['subjects']) as $i => $subject) {
            $subjects[] = $this->subject($subject, $i, $defaultTarget);
        }

        $overall = $this->overall($subjects, $terms, $defaultTarget);

        return [
            'student' => $raw['student'],
            'terms' => $terms,
            'overall' => $overall,
            'subjects' => $subjects,
            'focus_key' => $this->focusKey($subjects),
            'chart' => $this->chart($subjects, $overall),
            'assessments' => $this->assessments($subjects),
            'assessment_stats' => $this->assessmentStats($subjects),
        ];
    }

    /** South African CAPS achievement level: 7 = 80-100%, 6 = 70-79%, ... 1 = 0-29%. */
    public static function level(float $score): int
    {
        foreach ([80 => 7, 70 => 6, 60 => 5, 50 => 4, 40 => 3, 30 => 2] as $floor => $level) {
            if ($score >= $floor) {
                return $level;
            }
        }

        return 1;
    }

    public static function tone(float $score): string
    {
        return $score >= 70 ? 'good' : ($score >= 55 ? 'mid' : 'low');
    }

    // ------------------------------------------------------------------ subject
    private function subject(array $s, int $index, int $defaultTarget): array
    {
        $series = array_map('floatval', $s['term_averages']);
        $current = (int) round(end($series));
        $first = (int) round($series[0]);
        $target = (int) ($s['target'] ?? $defaultTarget);

        $topics = [];
        foreach ($s['topics'] as $name => $mastery) {
            $topics[] = ['name' => $name, 'mastery' => (int) $mastery, 'tone' => self::tone($mastery)];
        }
        usort($topics, fn ($a, $b) => $b['mastery'] <=> $a['mastery']);

        $strongest = array_slice(array_values(array_filter($topics, fn ($t) => $t['mastery'] >= 70)), 0, 3);
        $attention = array_values(array_filter(array_reverse($topics), fn ($t) => $t['mastery'] < 65));
        $attention = array_slice($attention, 0, 3);

        $papers = array_map(fn ($p) => $p + ['tone' => self::tone($p['score'])], $s['past_papers']);
        $quizScores = array_column($s['quizzes'], 'score');
        $attempted = (int) $s['questions']['attempted'];
        $correct = (int) $s['questions']['correct'];

        return [
            'key' => strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s['name']), '-')),
            'name' => $s['name'],
            'color' => self::PALETTE[$index % count(self::PALETTE)],
            'series' => array_map(fn ($v) => (int) round($v), $series),
            'current' => $current,
            'level' => self::level($current),
            'change' => $current - $first,
            'target' => $target,
            'target_status' => $this->targetStatus($current, $target),
            'topics' => $topics,
            'strongest' => $strongest,
            'attention' => $attention,
            'past_papers' => $papers,
            'past_paper_average' => $papers ? (int) round($this->mean(array_column($papers, 'score'))) : null,
            'quizzes' => $s['quizzes'],
            'quiz_stats' => [
                'completed' => count($quizScores),
                'average' => $quizScores ? (int) round($this->mean($quizScores)) : null,
                'highest' => $quizScores ? max($quizScores) : null,
                'lowest' => $quizScores ? min($quizScores) : null,
                'avg_attempts' => $s['quizzes'] ? round($this->mean(array_column($s['quizzes'], 'attempts')), 1) : null,
            ],
            'questions' => [
                'attempted' => $attempted,
                'correct' => $correct,
                'incorrect' => $attempted - $correct,
                'accuracy' => $attempted ? (int) round($correct / $attempted * 100) : null,
            ],
        ];
    }

    private function targetStatus(float $current, int $target): array
    {
        $gap = $target - $current;
        if ($gap <= 0) {
            return ['state' => 'met', 'tone' => 'good', 'label' => 'Goal met', 'detail' => 'Goal met'];
        }
        $pts = (int) ceil($gap);
        $detail = $pts.($pts === 1 ? ' pt' : ' pts').' to go';

        return $gap <= 5
            ? ['state' => 'close', 'tone' => 'mid', 'label' => 'Almost there', 'detail' => $detail]
            : ['state' => 'behind', 'tone' => 'low', 'label' => 'Below goal', 'detail' => $detail];
    }

    // ------------------------------------------------------------------ overall
    private function overall(array $subjects, array $terms, int $target): array
    {
        $current = $this->mean(array_column($subjects, 'current'));
        $termAverages = [];
        foreach (array_keys($terms) as $t) {
            $termAverages[] = (int) round($this->mean(array_map(fn ($s) => $s['series'][$t], $subjects)));
        }
        // Use the rounded figure the learner sees, so "74%" never comes with the wrong "pts to go".
        $status = $this->targetStatus(round($current), $target);

        return [
            'current' => (int) round($current),
            'level' => self::level(round($current)),
            'change' => $termAverages[count($termAverages) - 1] - $termAverages[0],
            'term_averages' => $termAverages,
            'current_term' => end($terms),
            'first_term' => $terms[0],
            'subject_count' => count($subjects),
            'at_target' => count(array_filter($subjects, fn ($s) => $s['target_status']['state'] === 'met')),
            'target' => $target,
            'target_status' => $status,
        ];
    }

    /** The subject furthest below its own target is opened by default. */
    private function focusKey(array $subjects): ?string
    {
        $best = null;
        $bestGap = -INF;
        foreach ($subjects as $s) {
            $gap = $s['target'] - $s['current'];
            if ($gap > $bestGap) {
                $bestGap = $gap;
                $best = $s['key'];
            }
        }

        return $best;
    }

    // ------------------------------------------------------------------- chart
    private function chart(array $subjects, array $overall): array
    {
        $series = [[
            'key' => 'overall', 'name' => 'Overall', 'color' => '#f8fafc',
            'points' => $overall['term_averages'], 'area' => true, 'labels' => true,
        ]];
        foreach ($subjects as $s) {
            $series[] = ['key' => $s['key'], 'name' => $s['name'], 'color' => $s['color'], 'points' => $s['series'], 'area' => false, 'labels' => false];
        }

        return ['series' => $series];
    }

    // ------------------------------------------------------------- assessments
    private function assessments(array $subjects): array
    {
        $rows = [];
        foreach ($subjects as $s) {
            foreach ($s['quizzes'] as $q) {
                $rows[] = $this->assessmentRow($s, $q['title'], 'quiz', $q['score'], $q['date']);
            }
            foreach ($s['past_papers'] as $p) {
                $rows[] = $this->assessmentRow($s, $p['title'], 'past_paper', $p['score'], $p['date']);
            }
        }
        usort($rows, fn ($a, $b) => [$b['date'], $a['title']] <=> [$a['date'], $b['title']]);

        return $rows;
    }

    private function assessmentRow(array $subject, string $title, string $type, int $score, string $date): array
    {
        return [
            'title' => $title,
            'type' => $type,
            'type_label' => $type === 'quiz' ? 'Quiz' : 'Past Paper',
            'subject' => $subject['name'],
            'subject_key' => $subject['key'],
            'score' => $score,
            'tone' => self::tone($score),
            'date' => $date,
            'date_label' => date('j M', strtotime($date.' UTC')),
        ];
    }

    /** One definition per metric, computed across all subjects. */
    private function assessmentStats(array $subjects): array
    {
        $scores = [];
        $quizzes = $papers = $attempted = $correct = 0;
        foreach ($subjects as $s) {
            $quizzes += count($s['quizzes']);
            $papers += count($s['past_papers']);
            array_push($scores, ...array_column($s['quizzes'], 'score'), ...array_column($s['past_papers'], 'score'));
            $attempted += $s['questions']['attempted'];
            $correct += $s['questions']['correct'];
        }

        return [
            'quizzes_completed' => $quizzes,
            'past_papers' => $papers,
            'average_score' => $scores ? (int) round($this->mean($scores)) : null,
            'quiz_accuracy' => $attempted ? (int) round($correct / $attempted * 100) : null,
            'questions_answered' => $attempted,
        ];
    }

    private function mean(array $values): float
    {
        return $values ? array_sum($values) / count($values) : 0.0;
    }
}
