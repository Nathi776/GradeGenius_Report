<?php

namespace Database\Seeders;

use App\Models\AssessmentResult;
use App\Models\LearnerSubject;
use App\Models\User;
use App\Support\DemoReportData;
use Illuminate\Database\Seeder;

/**
 * Loads the sample report into the real tables for the demo learner, so the demo exercises the same
 * code path as live data. Safe to run again: it only fills a learner that has no recorded subjects.
 */
class DemoMarksSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Demo seeders are disabled in production.');

            return;
        }

        $student = User::query()->where('email', 'test@example.com')->where('role', 'student')->first();

        if ($student === null || $student->learnerSubjects()->exists()) {
            return;
        }

        foreach (DemoReportData::forStudent()['subjects'] as $demo) {
            $subject = LearnerSubject::create([
                'student_id' => $student->id,
                'name' => $demo['name'],
                'target' => $demo['target'],
                'term_averages' => $demo['term_averages'],
                'questions_attempted' => $demo['questions']['attempted'],
                'questions_correct' => $demo['questions']['correct'],
            ]);

            foreach ($demo['topics'] as $topic => $mastery) {
                $subject->topicMasteries()->create(['topic' => $topic, 'mastery' => $mastery]);
            }

            foreach ($demo['past_papers'] as $paper) {
                $subject->assessmentResults()->create([
                    'kind' => AssessmentResult::KIND_PAST_PAPER,
                    'title' => $paper['title'],
                    'score' => $paper['score'],
                    'term' => 4,
                    'taken_on' => $paper['date'],
                ]);
            }

            foreach ($demo['quizzes'] as $quiz) {
                $subject->assessmentResults()->create([
                    'kind' => AssessmentResult::KIND_QUIZ,
                    'title' => $quiz['title'],
                    'score' => $quiz['score'],
                    'attempts' => $quiz['attempts'],
                    'term' => 4,
                    'taken_on' => $quiz['date'],
                ]);
            }
        }
    }
}
