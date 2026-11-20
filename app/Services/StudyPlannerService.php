<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Turns the ranked recommendation list into a short revision plan. Rules only, so a learner can
 * always see why a topic sits on a given day:
 *  - highest ranked topics are scheduled first, starting today;
 *  - a day holds at most DAILY_MINUTES of revision and at most MAX_PER_SUBJECT_PER_DAY topics from one subject;
 *  - a topic is never planned on or after the exam date of its subject (unless the exam is today, or tomorrow, when it is planned today).
 */
final class StudyPlannerService
{
    public const SESSION_MINUTES = 30;

    public const DAILY_MINUTES = 90;

    public const MAX_PER_SUBJECT_PER_DAY = 2;

    public const PLAN_DAYS = 7;

    /**
     * @param  array<int, array{subject_key: string, topic: string}>  $items  ranked recommendation items
     * @param  array<int, array{subject_key: string, date: string}>  $exams  upcoming exams
     * @return array<int, array{subject_key: string, topic: string, planned_date: string, minutes: int}>
     */
    public function build(array $items, array $exams = [], ?CarbonInterface $today = null): array
    {
        $start = CarbonImmutable::parse($today ?? now())->startOfDay();
        $perDay = intdiv(self::DAILY_MINUTES, self::SESSION_MINUTES);

        $examDates = [];
        foreach ($exams as $exam) {
            $key = $exam['subject_key'];
            if (! isset($examDates[$key]) || $exam['date'] < $examDates[$key]) {
                $examDates[$key] = $exam['date'];
            }
        }

        $load = array_fill(0, self::PLAN_DAYS, 0);
        $bySubject = array_fill(0, self::PLAN_DAYS, []);
        $sessions = [];

        foreach ($items as $item) {
            $examDate = $examDates[$item['subject_key']] ?? null;

            for ($day = 0; $day < self::PLAN_DAYS; $day++) {
                $date = $start->addDays($day);
                $isOnOrAfterExam = $examDate !== null && $date->toDateString() >= $examDate;

                if ($isOnOrAfterExam && $day > 0) {
                    break;
                }

                $subjectCount = $bySubject[$day][$item['subject_key']] ?? 0;
                if ($load[$day] >= $perDay || $subjectCount >= self::MAX_PER_SUBJECT_PER_DAY) {
                    continue;
                }

                $load[$day]++;
                $bySubject[$day][$item['subject_key']] = $subjectCount + 1;
                $sessions[] = [
                    'subject_key' => $item['subject_key'],
                    'topic' => $item['topic'],
                    'planned_date' => $date->toDateString(),
                    'minutes' => self::SESSION_MINUTES,
                ];

                break;
            }
        }

        usort($sessions, fn (array $a, array $b): int => $a['planned_date'] <=> $b['planned_date']);

        return $sessions;
    }
}
