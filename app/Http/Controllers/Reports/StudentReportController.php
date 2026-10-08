<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class StudentReportController extends Controller
{
    public function index()
    {
        return view('student.reports', [
            'progress' => [
                ['term' => 'Term 1', 'mark' => 58],
                ['term' => 'Term 2', 'mark' => 64],
                ['term' => 'Term 3', 'mark' => 69],
                ['term' => 'Term 4', 'mark' => 74],
            ],
            'subjects' => [
                ['name' => 'Mathematics', 'mark' => 78, 'grade' => 'B'],
                ['name' => 'Physical Sciences', 'mark' => 72, 'grade' => 'B'],
                ['name' => 'English Home Language', 'mark' => 81, 'grade' => 'A'],
                ['name' => 'Life Sciences', 'mark' => 68, 'grade' => 'C'],
                ['name' => 'Geography', 'mark' => 75, 'grade' => 'B'],
            ],
            'assessments' => [
                ['name' => 'Algebra Quiz', 'type' => 'Quiz', 'subject' => 'Mathematics', 'score' => 84, 'date' => '7 Oct'],
                ['name' => 'June Paper 2', 'type' => 'Past Paper', 'subject' => 'Mathematics', 'score' => 71, 'date' => '6 Oct'],
                ['name' => 'Chemical Reactions', 'type' => 'Quiz', 'subject' => 'Physical Sciences', 'score' => 79, 'date' => '5 Oct'],
                ['name' => '2024 NSC Paper 1', 'type' => 'Past Paper', 'subject' => 'English', 'score' => 82, 'date' => '4 Oct'],
            ],
            'topicMastery' => [
                ['name' => 'Algebra', 'mark' => 89],
                ['name' => 'Functions', 'mark' => 82],
                ['name' => 'Calculus', 'mark' => 74],
                ['name' => 'Trigonometry', 'mark' => 63],
                ['name' => 'Probability', 'mark' => 51],
                ['name' => 'Geometry', 'mark' => 58],
            ],
            'quizStats' => [
                ['label' => 'Quizzes completed', 'value' => '37'],
                ['label' => 'Average score', 'value' => '81%'],
                ['label' => 'Highest score', 'value' => '96%'],
                ['label' => 'Questions answered', 'value' => '428'],
                ['label' => 'Accuracy', 'value' => '79%'],
                ['label' => 'Average attempts', 'value' => '1.8'],
            ],
            'studyActivity' => [
                ['day' => 'Mon', 'minutes' => 80, 'time' => '1h 20m'],
                ['day' => 'Tue', 'minutes' => 48, 'time' => '48m'],
                ['day' => 'Wed', 'minutes' => 75, 'time' => '1h 15m'],
                ['day' => 'Thu', 'minutes' => 102, 'time' => '1h 42m'],
                ['day' => 'Fri', 'minutes' => 35, 'time' => '35m'],
                ['day' => 'Sat', 'minutes' => 91, 'time' => '1h 31m'],
                ['day' => 'Sun', 'minutes' => 71, 'time' => '1h 11m'],
            ],
            'readiness' => [
                ['name' => 'Mathematics', 'mark' => 81],
                ['name' => 'Physical Sciences', 'mark' => 72],
                ['name' => 'English', 'mark' => 86],
            ],
            'goals' => [
                ['name' => 'Mathematics target', 'current' => '78%', 'target' => '80%', 'progress' => 97, 'detail' => '2% remaining'],
                ['name' => 'Past papers', 'current' => '12', 'target' => '20', 'progress' => 60, 'detail' => '8 papers remaining'],
                ['name' => 'Weekly study goal', 'current' => '8h 42m', 'target' => '10h', 'progress' => 87, 'detail' => '1h 18m remaining'],
            ],
            'results' => [
                ['name' => 'Mathematics Quiz 12', 'score' => '84%', 'date' => '7 Oct'],
                ['name' => 'NSC Mathematics Paper 1', 'score' => '78%', 'date' => '6 Oct'],
                ['name' => 'Physical Sciences Quiz 8', 'score' => '72%', 'date' => '5 Oct'],
                ['name' => 'Life Sciences Past Paper 2024', 'score' => '68%', 'date' => '3 Oct'],
            ],
            'quizScores' => [65, 72, 68, 78, 85, 82, 90],
            'quizPerformanceBySubject' => [
                ['subject' => 'Mathematics', 'lowest' => 52, 'highest' => 96],
                ['subject' => 'Physical Sciences', 'lowest' => 64, 'highest' => 91],
                ['subject' => 'English Home Language', 'lowest' => 70, 'highest' => 94],
                ['subject' => 'Life Sciences', 'lowest' => 58, 'highest' => 88],
            ],
            'pastPaperPerformance' => [
                'Mathematics' => [
                    'papers' => [
                        ['name' => '2025 NSC Paper 1', 'score' => 78],
                        ['name' => '2024 NSC Paper 1', 'score' => 71],
                        ['name' => '2023 NSC Paper 1', 'score' => 84],
                        ['name' => '2022 NSC Paper 1', 'score' => 68],
                    ],
                    'average' => 75,
                    'attempted' => 150,
                    'correct' => 112,
                    'incorrect' => 38,
                    'strongest' => ['Algebra', 'Functions', 'Financial Mathematics'],
                    'attention' => ['Probability', 'Euclidean Geometry', 'Trigonometry'],
                ],
                'Physical Sciences' => [
                    'papers' => [
                        ['name' => '2025 NSC Paper 1', 'score' => 73],
                        ['name' => '2024 NSC Paper 1', 'score' => 69],
                        ['name' => '2023 NSC Paper 1', 'score' => 78],
                    ],
                    'average' => 73,
                    'attempted' => 112,
                    'correct' => 82,
                    'incorrect' => 30,
                    'strongest' => ['Mechanics', 'Waves'],
                    'attention' => ['Electricity', 'Chemical Systems'],
                ],
                'English Home Language' => [
                    'papers' => [
                        ['name' => '2025 NSC Paper 1', 'score' => 82],
                        ['name' => '2024 NSC Paper 1', 'score' => 79],
                        ['name' => '2023 NSC Paper 1', 'score' => 86],
                    ],
                    'average' => 82,
                    'attempted' => 96,
                    'correct' => 79,
                    'incorrect' => 17,
                    'strongest' => ['Comprehension', 'Summary Writing'],
                    'attention' => ['Language Structures'],
                ],
                'Life Sciences' => [
                    'papers' => [
                        ['name' => '2025 NSC Paper 1', 'score' => 68],
                        ['name' => '2024 NSC Paper 1', 'score' => 64],
                        ['name' => '2023 NSC Paper 1', 'score' => 72],
                    ],
                    'average' => 68,
                    'attempted' => 105,
                    'correct' => 71,
                    'incorrect' => 34,
                    'strongest' => ['Genetics', 'Evolution'],
                    'attention' => ['Human Physiology', 'Ecology'],
                ],
            ],
            'achievements' => [
                ['icon' => 'star', 'name' => 'First Past Paper'],
                ['icon' => 'target', 'name' => '80% Quiz Average'],
                ['icon' => 'book-open', 'name' => '10 Past Papers'],
                ['icon' => 'zap', 'name' => '7 Day Streak'],
                ['icon' => 'trending-up', 'name' => 'Improved by 10%'],
            ],
        ]);
    }
}
