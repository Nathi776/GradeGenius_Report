<?php

namespace App\Services;

use App\Models\AssessmentResult;
use App\Models\LearnerSubject;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Records one quiz or past-paper mark for a learner and keeps the subject's term averages in step.
 */
final class LearnerResultRecorder
{
    /**
     * @param  array{kind: string, title: string, score: int, term: int, taken_on: string, attempts?: int|null, topic?: string|null, mastery?: int|null, questions_attempted?: int|null, questions_correct?: int|null}  $data
     */
    public function record(User $teacher, User $student, SchoolClass $schoolClass, array $data): AssessmentResult
    {
        return DB::transaction(function () use ($teacher, $student, $schoolClass, $data): AssessmentResult {
            $subject = LearnerSubject::query()->firstOrCreate(
                ['student_id' => $student->id, 'name' => $schoolClass->subject],
                ['target' => $schoolClass->target],
            );

            $result = $subject->assessmentResults()->create([
                'recorded_by' => $teacher->id,
                'kind' => $data['kind'],
                'title' => $data['title'],
                'score' => $data['score'],
                'attempts' => $data['kind'] === AssessmentResult::KIND_QUIZ ? ($data['attempts'] ?? 1) : null,
                'term' => $data['term'],
                'taken_on' => $data['taken_on'],
            ]);

            if (! empty($data['topic']) && isset($data['mastery'])) {
                $subject->topicMasteries()->updateOrCreate(['topic' => $data['topic']], ['mastery' => $data['mastery']]);
            }

            $attempted = (int) ($data['questions_attempted'] ?? 0);
            if ($attempted > 0) {
                $subject->questions_attempted += $attempted;
                $subject->questions_correct += min((int) ($data['questions_correct'] ?? 0), $attempted);
            }

            $subject->term_averages = $this->recalculatedAverages($subject);
            $subject->save();

            return $result;
        });
    }

    /**
     * @return list<int|null>
     */
    private function recalculatedAverages(LearnerSubject $subject): array
    {
        $averages = [];
        foreach (range(1, count(LearnerReportRepository::TERMS)) as $term) {
            $scores = $subject->assessmentResults()->where('term', $term)->pluck('score');
            $averages[] = $scores->isEmpty() ? null : (int) round($scores->avg());
        }

        return $averages;
    }
}
