<?php

namespace App\Services;

final class StudentReportService
{
    private const PALETTE = ['#5b7cfa', '#4ade80', '#fdba74', '#a78bfa', '#2dd4bf', '#f472b6', '#facc15'];

    public function build(array $raw): array
    {
        $terms = $raw['terms'];
        $defaultTarget = (int) ($raw['target'] ?? 75);
        $subjects = [];

        foreach (array_values($raw['subjects']) as $index => $subject) {
            $subjects[] = $this->subject($subject, $index, $defaultTarget);
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

    public static function tone(float $score): string
    {
        return $score >= 70 ? 'good' : ($score >= 55 ? 'mid' : 'low');
    }

    private function focusKey(array $subjects): ?string
    {
        $focusKey = null;
        $largestGap = -INF;

        foreach ($subjects as $subject) {
            $gap = $subject['target'] - $subject['current'];

            if ($gap > $largestGap) {
                $largestGap = $gap;
                $focusKey = $subject['key'];
            }
        }

        return $focusKey;
    }

    private function subject(array $subject, int $index, int $defaultTarget): array
    {
        $series = array_map('floatval', $subject['term_averages']);
        $current = (int) round(end($series));
        $first = (int) round($series[0]);
        $target = (int) ($subject['target'] ?? $defaultTarget);
        $topics = [];

        foreach ($subject['topics'] as $name => $mastery) {
            $topics[] = ['name' => $name, 'mastery' => (int) $mastery, 'tone' => self::tone($mastery)];
        }

        usort($topics, fn (array $left, array $right): int => $right['mastery'] <=> $left['mastery']);
        $quizScores = array_column($subject['quizzes'], 'score');
        $attempted = (int) $subject['questions']['attempted'];
        $correct = (int) $subject['questions']['correct'];

        return [
            'key' => strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $subject['name']), '-')),
            'name' => $subject['name'],
            'color' => self::PALETTE[$index % count(self::PALETTE)],
            'series' => array_map(fn (float $value): int => (int) round($value), $series),
            'current' => $current,
            'change' => $current - $first,
            'target' => $target,
            'target_status' => $this->targetStatus($current, $target),
            'topics' => $topics,
            'strongest' => array_slice(array_values(array_filter($topics, fn (array $topic): bool => $topic['mastery'] >= 70)), 0, 3),
            'attention' => array_slice(array_values(array_filter(array_reverse($topics), fn (array $topic): bool => $topic['mastery'] < 65)), 0, 3),
            'past_papers' => $subject['past_papers'],
            'past_paper_average' => $subject['past_papers'] ? (int) round($this->mean(array_column($subject['past_papers'], 'score'))) : null,
            'quizzes' => $subject['quizzes'],
            'quiz_stats' => [
                'completed' => count($quizScores),
                'average' => $quizScores ? (int) round($this->mean($quizScores)) : null,
                'highest' => $quizScores ? max($quizScores) : null,
                'lowest' => $quizScores ? min($quizScores) : null,
                'avg_attempts' => $subject['quizzes'] ? round($this->mean(array_column($subject['quizzes'], 'attempts')), 1) : null,
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

        $points = (int) ceil($gap);
        $detail = $points.($points === 1 ? ' pt' : ' pts').' to go';

        return $gap <= 5
            ? ['state' => 'close', 'tone' => 'mid', 'label' => 'Almost there', 'detail' => $detail]
            : ['state' => 'behind', 'tone' => 'low', 'label' => 'Below goal', 'detail' => $detail];
    }

    private function overall(array $subjects, array $terms, int $target): array
    {
        $current = $this->mean(array_column($subjects, 'current'));
        $termAverages = [];

        foreach (array_keys($terms) as $term) {
            $termAverages[] = (int) round($this->mean(array_map(fn (array $subject): int => $subject['series'][$term], $subjects)));
        }

        return [
            'current' => (int) round($current),
            'change' => end($termAverages) - $termAverages[0],
            'term_averages' => $termAverages,
            'current_term' => end($terms),
            'first_term' => $terms[0],
            'subject_count' => count($subjects),
            'at_target' => count(array_filter($subjects, fn (array $subject): bool => $subject['target_status']['state'] === 'met')),
            'target' => $target,
            'target_status' => $this->targetStatus(round($current), $target),
        ];
    }

    private function chart(array $subjects, array $overall): array
    {
        $series = [['key' => 'overall', 'name' => 'Overall', 'color' => '#f8fafc', 'points' => $overall['term_averages'], 'area' => true]];

        foreach ($subjects as $subject) {
            $series[] = ['key' => $subject['key'], 'name' => $subject['name'], 'color' => $subject['color'], 'points' => $subject['series'], 'area' => false];
        }

        return ['series' => $series];
    }

    private function assessments(array $subjects): array
    {
        $rows = [];

        foreach ($subjects as $subject) {
            foreach ($subject['quizzes'] as $quiz) {
                $rows[] = $this->assessmentRow($subject, $quiz['title'], 'quiz', $quiz['score'], $quiz['date']);
            }
            foreach ($subject['past_papers'] as $paper) {
                $rows[] = $this->assessmentRow($subject, $paper['title'], 'past_paper', $paper['score'], $paper['date']);
            }
        }

        usort($rows, fn (array $left, array $right): int => [$right['date'], $left['title']] <=> [$left['date'], $right['title']]);

        return $rows;
    }

    private function assessmentRow(array $subject, string $title, string $type, int $score, string $date): array
    {
        return [
            'title' => $title,
            'type' => $type,
            'type_label' => $type === 'quiz' ? 'Quiz' : 'Past Paper',
            'subject' => $subject['name'],
            'score' => $score,
            'tone' => self::tone($score),
            'date' => $date,
            'date_label' => date('j M', strtotime($date.' UTC')),
        ];
    }

    private function assessmentStats(array $subjects): array
    {
        $scores = [];
        $quizzes = $papers = $attempted = $correct = 0;

        foreach ($subjects as $subject) {
            $quizzes += count($subject['quizzes']);
            $papers += count($subject['past_papers']);
            $scores = array_merge($scores, array_column($subject['quizzes'], 'score'), array_column($subject['past_papers'], 'score'));
            $attempted += $subject['questions']['attempted'];
            $correct += $subject['questions']['correct'];
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
