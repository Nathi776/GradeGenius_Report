<?php

namespace App\Services;

use App\Models\Exam;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Upcoming exams for one grade, read from the exams table.
 *
 * Returns plain arrays so the dashboard and the recommendation rules can use them without touching
 * Eloquent. Past exams are never returned.
 */
final class ExamScheduleService
{
    /**
     * @param  array<string, string>  $subjectNames  Subject key => display name, from the student report
     * @return list<array{id: int, subject_key: string, subject: string, title: string, paper: ?string, date: string, date_label: string, days_until: int, countdown: string}>
     */
    public function upcomingForGrade(int $grade, array $subjectNames = [], ?CarbonInterface $today = null, int $limit = 5): array
    {
        $today = CarbonImmutable::parse($today ?? now())->startOfDay();

        return Exam::query()
            ->where('grade', $grade)
            ->whereDate('exam_date', '>=', $today->toDateString())
            ->orderBy('exam_date')
            ->orderBy('subject_key')
            ->limit($limit)
            ->get()
            ->map(function (Exam $exam) use ($today, $subjectNames): array {
                $date = CarbonImmutable::parse($exam->exam_date)->startOfDay();
                $days = (int) $today->diffInDays($date);

                return [
                    'id' => $exam->id,
                    'subject_key' => $exam->subject_key,
                    'subject' => $subjectNames[$exam->subject_key] ?? str($exam->subject_key)->replace('-', ' ')->title()->toString(),
                    'title' => $exam->title,
                    'paper' => $exam->paper,
                    'date' => $date->toDateString(),
                    'date_label' => $date->format('D j M'),
                    'days_until' => $days,
                    'countdown' => match (true) {
                        $days === 0 => 'Today',
                        $days === 1 => 'Tomorrow',
                        default => 'In '.$days.' days',
                    },
                ];
            })
            ->all();
    }
}
