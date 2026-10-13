<?php

namespace App\Support;

/**
 * Demo data in the raw shape StudentReportService expects.
 *
 * Replace forStudent() with a query against your real tables. The report page never needs to
 * change: it renders whatever subjects this returns, so a learner with 4 or 7 subjects just works.
 */
final class DemoReportData
{
    public static function forStudent(?string $today = null): array
    {
        // Dates are relative to today so the "recent activity" numbers never go stale in the demo.
        $d = fn (int $daysAgo): string => (new \DateTimeImmutable($today ?? 'now', new \DateTimeZone('UTC')))
            ->modify("-{$daysAgo} days")->format('Y-m-d');

        return [
            'student' => ['name' => 'Nathi', 'grade' => 12],
            'terms' => ['Term 1', 'Term 2', 'Term 3', 'Term 4'],
            'target' => 75,
            'subjects' => [
                [
                    'name' => 'Mathematics',
                    'term_averages' => [62, 68, 72, 78],
                    'target' => 75,
                    'questions' => ['attempted' => 150, 'correct' => 112],
                    'topics' => [
                        'Algebra' => 89, 'Functions' => 82, 'Calculus' => 74,
                        'Trigonometry' => 63, 'Geometry' => 58, 'Probability' => 51,
                    ],
                    'past_papers' => [
                        self::paper('2025 NSC Paper 1', 78, $d(2)), self::paper('2024 NSC Paper 1', 71, $d(18)),
                        self::paper('2023 NSC Paper 1', 84, $d(36)), self::paper('2022 NSC Paper 1', 68, $d(51)),
                    ],
                    'quizzes' => [
                        self::quiz('Algebra Quiz', 84, 1, $d(1)), self::quiz('Functions Quiz', 79, 2, $d(6)),
                        self::quiz('Calculus Intro', 66, 2, $d(10)), self::quiz('Trig Identities', 61, 3, $d(14)),
                        self::quiz('Probability Basics', 52, 3, $d(19)), self::quiz('Euclidean Geometry', 58, 2, $d(23)),
                        self::quiz('Financial Maths', 88, 1, $d(27)), self::quiz('Statistics', 74, 2, $d(32)),
                        self::quiz('Sequences & Series', 81, 1, $d(37)), self::quiz('Algebra Review', 90, 1, $d(42)),
                    ],
                ],
                [
                    'name' => 'Physical Sciences',
                    'term_averages' => [60, 64, 67, 71],
                    'target' => 70,
                    'questions' => ['attempted' => 120, 'correct' => 84],
                    'topics' => [
                        'Mechanics' => 78, 'Momentum' => 70, 'Electricity' => 66,
                        'Waves' => 62, 'Chemical Change' => 58, 'Organic Chemistry' => 54,
                    ],
                    'past_papers' => [
                        self::paper('2025 NSC Paper 1', 72, $d(7)), self::paper('2024 NSC Paper 2', 65, $d(25)),
                        self::paper('2023 NSC Paper 1', 70, $d(39)),
                    ],
                    'quizzes' => [
                        self::quiz('Chemical Reactions', 79, 1, $d(3)), self::quiz('Newton\'s Laws', 83, 1, $d(7)),
                        self::quiz('Circuits', 64, 2, $d(13)), self::quiz('Momentum & Impulse', 72, 2, $d(20)),
                        self::quiz('Organic Naming', 55, 3, $d(26)), self::quiz('Wave Properties', 68, 2, $d(33)),
                        self::quiz('Acids & Bases', 61, 2, $d(40)), self::quiz('Work & Energy', 77, 1, $d(47)),
                    ],
                ],
                [
                    'name' => 'English Home Language',
                    'term_averages' => [70, 72, 73, 76],
                    'target' => 75,
                    'questions' => ['attempted' => 90, 'correct' => 68],
                    'topics' => [
                        'Comprehension' => 84, 'Language Structures' => 79, 'Literature' => 74,
                        'Visual Literacy' => 71, 'Essay Writing' => 69, 'Poetry' => 62,
                    ],
                    'past_papers' => [
                        self::paper('2024 NSC Paper 1', 82, $d(4)), self::paper('2023 NSC Paper 1', 77, $d(22)),
                        self::paper('2022 NSC Paper 1', 74, $d(44)),
                    ],
                    'quizzes' => [
                        self::quiz('Comprehension Skills', 85, 1, $d(5)), self::quiz('Figures of Speech', 78, 1, $d(11)),
                        self::quiz('Poetry Analysis', 66, 2, $d(18)), self::quiz('Essay Structure', 72, 2, $d(28)),
                        self::quiz('Grammar Check', 88, 1, $d(36)), self::quiz('Visual Texts', 74, 1, $d(45)),
                    ],
                ],
                [
                    'name' => 'Life Sciences',
                    'term_averages' => [66, 72, 78, 82],
                    'target' => 75,
                    'questions' => ['attempted' => 110, 'correct' => 88],
                    'topics' => [
                        'Genetics' => 85, 'Evolution' => 80, 'Ecology' => 78,
                        'Human Reproduction' => 74, 'Human Endocrine System' => 66, 'Plant Responses' => 61,
                    ],
                    'past_papers' => [
                        self::paper('2025 NSC Paper 1', 81, $d(8)), self::paper('2024 NSC Paper 2', 79, $d(29)),
                        self::paper('2023 NSC Paper 1', 76, $d(48)),
                    ],
                    'quizzes' => [
                        self::quiz('DNA & Genetics', 89, 1, $d(4)), self::quiz('Natural Selection', 84, 1, $d(9)),
                        self::quiz('Ecosystems', 80, 1, $d(17)), self::quiz('Hormones', 67, 2, $d(24)),
                        self::quiz('Meiosis', 86, 1, $d(30)), self::quiz('Human Reproduction', 77, 2, $d(37)),
                        self::quiz('Plant Responses', 62, 3, $d(43)), self::quiz('Evolution Evidence', 83, 1, $d(50)),
                    ],
                ],
                [
                    'name' => 'Accounting',
                    'term_averages' => [58, 60, 57, 62],
                    'target' => 70,
                    'questions' => ['attempted' => 80, 'correct' => 46],
                    'topics' => [
                        'Ledgers' => 72, 'Financial Statements' => 70, 'Budgets' => 64,
                        'Inventory Valuation' => 60, 'Cash Flow' => 56, 'Reconciliations' => 52,
                    ],
                    'past_papers' => [
                        self::paper('2025 NSC Paper 1', 64, $d(12)), self::paper('2024 NSC Paper 1', 58, $d(34)),
                    ],
                    'quizzes' => [
                        self::quiz('Cash Flow Statement', 55, 3, $d(6)), self::quiz('Bank Reconciliation', 50, 3, $d(15)),
                        self::quiz('Budget Variances', 66, 2, $d(25)), self::quiz('Journals & Ledgers', 74, 1, $d(35)),
                        self::quiz('Inventory Methods', 61, 2, $d(46)),
                    ],
                ],
            ],
        ];
    }

    /**
     * The children linked to a parent, keyed by a URL-safe id. Replace with the signed-in parent's
     * children from the database (and authorise every request against that link).
     *
     * @return array<string, array>
     */
    public static function childrenForParent(?string $today = null): array
    {
        $nathi = self::forStudent($today);
        $subjects = $nathi['subjects'];

        $sipho = $nathi;
        $sipho['student'] = ['name' => 'Sipho', 'grade' => 10];
        $sipho['subjects'] = [
            ['term_averages' => [55, 57, 60, 61], 'target' => 70] + $subjects[0],
            ['term_averages' => [74, 76, 79, 80], 'target' => 75] + $subjects[3],
            ['term_averages' => [68, 70, 69, 72], 'target' => 70] + $subjects[2],
        ];

        return ['nathi' => $nathi, 'sipho' => $sipho];
    }

    private static function quiz(string $title, int $score, int $attempts, string $date): array
    {
        return compact('title', 'score', 'attempts', 'date');
    }

    private static function paper(string $title, int $score, string $date): array
    {
        return compact('title', 'score', 'date');
    }
}
