<?php

namespace App\Services;

use App\Models\LearnerSubject;
use App\Models\User;

/**
 * Builds the raw report array StudentReportService expects from a learner's recorded marks.
 *
 * Returns null when the learner has no recorded subjects yet, so callers can show an honest empty
 * state instead of invented numbers.
 */
final class LearnerReportRepository
{
    public const TERMS = ['Term 1', 'Term 2', 'Term 3', 'Term 4'];

    public const DEFAULT_GRADE = 12;

    /**
     * @return array<string, mixed>|null
     */
    public function forStudent(User $student): ?array
    {
        $subjects = $student->learnerSubjects()
            ->with(['topicMasteries', 'assessmentResults'])
            ->orderBy('name')
            ->get()
            ->filter(fn (LearnerSubject $subject): bool => $this->hasAnyAverage($subject));

        if ($subjects->isEmpty()) {
            return null;
        }

        return [
            'student' => ['name' => $student->name, 'grade' => $this->gradeFor($student)],
            'terms' => self::TERMS,
            'target' => 75,
            'subjects' => $subjects->map(fn (LearnerSubject $subject): array => $this->subjectArray($subject))->values()->all(),
        ];
    }

    /**
     * The learner's mark series for one subject, filled so every term has a number, or null when none recorded.
     *
     * @return list<int>|null
     */
    public function termAveragesFor(User $student, string $subjectName): ?array
    {
        $subject = $student->learnerSubjects()->whereRaw('lower(name) = ?', [mb_strtolower($subjectName)])->first();

        return $subject !== null && $this->hasAnyAverage($subject) ? $this->filledAverages($subject) : null;
    }

    private function gradeFor(User $student): int
    {
        return (int) ($student->enrolledClasses()->max('grade') ?? self::DEFAULT_GRADE);
    }

    private function hasAnyAverage(LearnerSubject $subject): bool
    {
        return array_filter($subject->term_averages ?? [], fn ($mark): bool => $mark !== null) !== [];
    }

    /**
     * @return list<int>
     */
    private function filledAverages(LearnerSubject $subject): array
    {
        $stored = array_pad(array_values($subject->term_averages ?? []), count(self::TERMS), null);
        $first = null;
        foreach ($stored as $mark) {
            if ($mark !== null) {
                $first = (int) $mark;
                break;
            }
        }

        $filled = [];
        $previous = $first;
        foreach (array_slice($stored, 0, count(self::TERMS)) as $mark) {
            $previous = $mark === null ? $previous : (int) $mark;
            $filled[] = (int) $previous;
        }

        return $filled;
    }

    /**
     * @return array<string, mixed>
     */
    private function subjectArray(LearnerSubject $subject): array
    {
        $results = $subject->assessmentResults->sortByDesc('taken_on');
        $row = fn ($result): array => [
            'title' => $result->title,
            'score' => $result->score,
            'date' => $result->taken_on->toDateString(),
        ];

        $array = [
            'name' => $subject->name,
            'term_averages' => $this->filledAverages($subject),
            'questions' => ['attempted' => $subject->questions_attempted, 'correct' => min($subject->questions_correct, $subject->questions_attempted)],
            'topics' => $subject->topicMasteries->sortBy('topic')->pluck('mastery', 'topic')->map(fn ($mastery): int => (int) $mastery)->all(),
            'past_papers' => $results->where('kind', 'past_paper')->map($row)->values()->all(),
            'quizzes' => $results->where('kind', 'quiz')->map(fn ($result): array => $row($result) + ['attempts' => $result->attempts ?? 1])->values()->all(),
        ];

        if ($subject->target !== null) {
            $array['target'] = $subject->target;
        }

        return $array;
    }
}
