<?php

namespace App\Support;

final class DemoReportData
{
    public static function forStudent(): array
    {
        return [
            'student' => ['name' => 'Nathi', 'grade' => 12],
            'terms' => ['Term 1', 'Term 2', 'Term 3', 'Term 4'],
            'target' => 75,
            'subjects' => [
                self::subject('Mathematics', [62, 68, 72, 78], 75, 150, 112, [
                    'Algebra' => 89, 'Functions' => 82, 'Calculus' => 74,
                    'Trigonometry' => 63, 'Geometry' => 58, 'Probability' => 51,
                ]),
                self::subject('Physical Sciences', [60, 64, 67, 71], 70, 120, 84, [
                    'Mechanics' => 78, 'Momentum' => 70, 'Electricity' => 66,
                    'Waves' => 62, 'Chemical Change' => 58, 'Organic Chemistry' => 54,
                ]),
                self::subject('English Home Language', [70, 72, 73, 76], 75, 90, 68, [
                    'Comprehension' => 84, 'Language Structures' => 79, 'Literature' => 74,
                    'Visual Literacy' => 71, 'Essay Writing' => 69, 'Poetry' => 62,
                ]),
                self::subject('Life Sciences', [66, 72, 78, 82], 75, 110, 88, [
                    'Genetics' => 85, 'Evolution' => 80, 'Ecology' => 78,
                    'Human Reproduction' => 74, 'Human Endocrine System' => 66, 'Plant Responses' => 61,
                ]),
                self::subject('Accounting', [58, 60, 57, 62], 70, 80, 46, [
                    'Ledgers' => 72, 'Financial Statements' => 70, 'Budgets' => 64,
                    'Inventory Valuation' => 60, 'Cash Flow' => 56, 'Reconciliations' => 52,
                ]),
            ],
        ];
    }

    private static function subject(string $name, array $termAverages, int $target, int $attempted, int $correct, array $topics): array
    {
        return [
            'name' => $name,
            'term_averages' => $termAverages,
            'target' => $target,
            'questions' => ['attempted' => $attempted, 'correct' => $correct],
            'topics' => $topics,
            'past_papers' => [
                ['title' => '2025 NSC Paper 1', 'score' => max(45, $termAverages[3] + 2), 'date' => '2026-10-06'],
                ['title' => '2024 NSC Paper 1', 'score' => max(42, $termAverages[2] - 1), 'date' => '2026-09-20'],
            ],
            'quizzes' => [
                ['title' => $name.' Concepts Quiz', 'score' => min(96, $termAverages[3] + 6), 'attempts' => 1, 'date' => '2026-10-07'],
                ['title' => $name.' Revision Quiz', 'score' => max(45, $termAverages[2] + 1), 'attempts' => 2, 'date' => '2026-09-28'],
            ],
        ];
    }
}
