<?php

namespace App\Services;

/**
 * Rule-based learning recommendations for one student, built from an already-built student report and
 * the upcoming exams for the student's grade (plain arrays in, plain arrays out).
 *
 * Every recommendation carries the reasons that produced it, so the student can see why a topic is
 * on the list. Nothing here is predicted or generated: a topic is listed when its mastery is below
 * WEAK_BELOW, and ranked by how far below it is, with a boost when an exam for that subject is close.
 */
final class LearningRecommendationService
{
    public const WEAK_BELOW = 65;

    public const HIGH_BELOW = 55;

    public const EXAM_SOON_DAYS = 14;

    private const EXAM_NEAR_DAYS = 30;

    private const SOON_BOOST = 25;

    private const NEAR_BOOST = 10;

    /**
     * @param  array<string, mixed>  $report  Output of StudentReportService::build()
     * @param  list<array<string, mixed>>  $exams  Output of ExamScheduleService::upcomingForGrade(), soonest first
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>, criteria: array<string, int>}
     */
    public function build(array $report, array $exams = []): array
    {
        $nextExamBySubject = [];
        foreach ($exams as $exam) {
            $nextExamBySubject[$exam['subject_key']] ??= $exam;
        }

        $items = [];
        foreach ($report['subjects'] as $subject) {
            $exam = $nextExamBySubject[$subject['key']] ?? null;
            foreach ($subject['topics'] as $topic) {
                if ($topic['mastery'] >= self::WEAK_BELOW) {
                    continue;
                }
                $items[] = $this->item($subject, $topic, $exam);
            }
        }

        usort($items, fn (array $a, array $b): int => [$b['score'], $a['subject'], $a['topic']] <=> [$a['score'], $b['subject'], $b['topic']]);

        return [
            'items' => $items,
            'summary' => [
                'weak_topics' => count($items),
                'high_priority' => count(array_filter($items, fn (array $i): bool => $i['priority'] === 'High')),
                'subjects' => count(array_unique(array_column($items, 'subject_key'))),
                'exams_soon' => count(array_filter($exams, fn (array $e): bool => $e['days_until'] <= self::EXAM_SOON_DAYS)),
            ],
            'criteria' => [
                'weak_below' => self::WEAK_BELOW,
                'high_below' => self::HIGH_BELOW,
                'exam_soon_days' => self::EXAM_SOON_DAYS,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $subject
     * @param  array<string, mixed>  $topic
     * @param  array<string, mixed>|null  $exam
     * @return array<string, mixed>
     */
    private function item(array $subject, array $topic, ?array $exam): array
    {
        $mastery = $topic['mastery'];
        $examDays = $exam['days_until'] ?? null;
        $examSoon = $examDays !== null && $examDays <= self::EXAM_SOON_DAYS;
        $examNear = $examDays !== null && ! $examSoon && $examDays <= self::EXAM_NEAR_DAYS;

        $score = (self::WEAK_BELOW - $mastery) + ($examSoon ? self::SOON_BOOST : ($examNear ? self::NEAR_BOOST : 0));
        $isHigh = $mastery < self::HIGH_BELOW || ($examSoon && $mastery < self::WEAK_BELOW);

        $reasons = ['Topic mastery is '.$mastery.'%, below the '.self::WEAK_BELOW.'% mark.'];
        $gap = $subject['target'] - $subject['current'];
        $reasons[] = $gap > 0
            ? $subject['name'].' is at '.$subject['current'].'%, '.$gap.' '.($gap === 1 ? 'point' : 'points').' below your '.$subject['target'].'% goal.'
            : $subject['name'].' is at goal ('.$subject['current'].'%), but this topic is holding it back.';
        if ($subject['questions']['accuracy'] !== null && $subject['questions']['accuracy'] < 70) {
            $reasons[] = 'Question accuracy in '.$subject['name'].' is '.$subject['questions']['accuracy'].'%.';
        }
        if ($exam !== null && ($examSoon || $examNear)) {
            $reasons[] = $exam['title'].($exam['paper'] ? ' ('.$exam['paper'].')' : '').' is '.strtolower($exam['countdown']).' ('.$exam['date_label'].').';
        }

        return [
            'subject' => $subject['name'],
            'subject_key' => $subject['key'],
            'color' => $subject['color'],
            'topic' => $topic['name'],
            'mastery' => $mastery,
            'score' => $score,
            'priority' => $isHigh ? 'High' : 'Medium',
            'tone' => $isHigh ? 'low' : 'mid',
            'reasons' => $reasons,
            'approach' => $mastery < self::HIGH_BELOW
                ? 'Go back to the basics of '.$topic['name'].': work through the core examples first, then try past-paper questions.'
                : 'Do past-paper questions on '.$topic['name'].' and review every mistake.',
        ];
    }
}
