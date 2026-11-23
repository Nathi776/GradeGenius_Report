<?php

namespace App\Services;

use App\Models\AssessmentResult;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Imports a class's marks from a CSV file. All rows are checked first; nothing is saved unless every row is valid.
 */
final class ClassMarksImporter
{
    public const MAX_ROWS = 500;

    public const HEADERS = ['email', 'type', 'title', 'mark', 'term', 'date', 'attempts', 'topic', 'topic_mastery'];

    public function __construct(private readonly LearnerResultRecorder $recorder) {}

    /**
     * @param  resource  $handle
     * @return array{imported: int, errors: list<string>}
     */
    public function import(User $teacher, SchoolClass $schoolClass, $handle): array
    {
        $header = fgetcsv($handle);
        $columns = array_map(fn ($name): string => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $name))), $header ?: []);

        $missing = array_diff(['email', 'type', 'title', 'mark', 'term', 'date'], $columns);
        if ($missing !== []) {
            return ['imported' => 0, 'errors' => ['The first row must be a header with the columns: '.implode(', ', self::HEADERS).'. Missing: '.implode(', ', $missing).'.']];
        }

        $learners = $schoolClass->learners()->get()->keyBy(fn (User $learner): string => strtolower($learner->email));
        $rows = [];
        $errors = [];
        $line = 1;

        while (($cells = fgetcsv($handle)) !== false) {
            $line++;
            if ($cells === [null] || implode('', array_map('trim', $cells)) === '') {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                $errors[] = 'Too many rows: a file can hold at most '.self::MAX_ROWS.' marks.';
                break;
            }

            $row = array_map(fn ($value): string => trim((string) $value), array_combine($columns, array_pad(array_slice($cells, 0, count($columns)), count($columns), '')));
            $learner = $learners->get(strtolower($row['email']));

            if ($learner === null) {
                $errors[] = "Line {$line}: {$row['email']} is not a learner in this class.";

                continue;
            }

            $validator = Validator::make([
                'kind' => match (strtolower($row['type'])) {
                    'quiz', 'test' => AssessmentResult::KIND_QUIZ,
                    'past paper', 'past_paper', 'paper' => AssessmentResult::KIND_PAST_PAPER,
                    default => $row['type'],
                },
                'title' => $row['title'],
                'score' => $row['mark'],
                'term' => $row['term'],
                'taken_on' => $row['date'],
                'attempts' => ($row['attempts'] ?? '') === '' ? null : $row['attempts'],
                'topic' => ($row['topic'] ?? '') === '' ? null : $row['topic'],
                'mastery' => ($row['topic_mastery'] ?? '') === '' ? null : $row['topic_mastery'],
            ], [
                'kind' => ['required', 'in:'.AssessmentResult::KIND_QUIZ.','.AssessmentResult::KIND_PAST_PAPER],
                'title' => ['required', 'string', 'max:255'],
                'score' => ['required', 'integer', 'between:0,100'],
                'term' => ['required', 'integer', 'between:1,'.count(LearnerReportRepository::TERMS)],
                'taken_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
                'attempts' => ['nullable', 'integer', 'between:1,255'],
                'topic' => ['nullable', 'string', 'max:120', 'required_with:mastery'],
                'mastery' => ['nullable', 'integer', 'between:0,100', 'required_with:topic'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Line {$line}: ".$validator->errors()->first();

                continue;
            }

            $rows[] = [$learner, $validator->validated()];
        }

        if ($errors === [] && $rows === []) {
            $errors[] = 'The file has no marks in it.';
        }

        if ($errors !== []) {
            return ['imported' => 0, 'errors' => $errors];
        }

        DB::transaction(function () use ($rows, $teacher, $schoolClass): void {
            foreach ($rows as [$learner, $data]) {
                $this->recorder->record($teacher, $learner, $schoolClass, $data);
            }
        });

        return ['imported' => count($rows), 'errors' => []];
    }
}
