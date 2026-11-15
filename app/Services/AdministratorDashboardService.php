<?php

namespace App\Services;

/**
 * School-wide academic intelligence for school leadership, principals, and department heads.
 */
final class AdministratorDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $terms = ['Term 1', 'Term 2', 'Term 3', 'Term 4'];

        $snapshot = [
            'total_learners' => 2341,
            'school_average' => 67.8,
            'change' => 4.2,
            'first_term' => 'Term 1',
            'pass_rate' => 91.4,
            'bachelor_pass_rate' => 64.2,
            'at_risk_count' => 86,
            'top_achievers' => 312,
            'reports_generated' => 1847,
        ];

        $cohorts = [
            [
                'grade' => 'Grade 12 (NSC Matric)',
                'learners' => 482,
                'average' => 71.4,
                'target' => 75.0,
                'pass_rate' => 94.6,
                'bachelor_rate' => 72.1,
                'attention' => 14,
                'tone' => 'good',
            ],
            [
                'grade' => 'Grade 11',
                'learners' => 524,
                'average' => 66.8,
                'target' => 70.0,
                'pass_rate' => 90.2,
                'bachelor_rate' => 62.4,
                'attention' => 28,
                'tone' => 'mid',
            ],
            [
                'grade' => 'Grade 10',
                'learners' => 615,
                'average' => 64.1,
                'target' => 68.0,
                'pass_rate' => 88.5,
                'bachelor_rate' => 58.0,
                'attention' => 44,
                'tone' => 'mid',
            ],
            [
                'grade' => 'Grade 8 & 9 (GET Band)',
                'learners' => 720,
                'average' => 69.2,
                'target' => 70.0,
                'pass_rate' => 92.4,
                'bachelor_rate' => 66.5,
                'attention' => 0,
                'tone' => 'good',
            ],
        ];

        $departments = [
            [
                'name' => 'Mathematics',
                'head' => 'Mrs. D. Naidoo',
                'average' => 64.2,
                'target' => 70.0,
                'change' => 3.8,
                'pass_rate' => 86.4,
                'tone' => 'mid',
            ],
            [
                'name' => 'Physical Sciences',
                'head' => 'Mr. K. Sithole',
                'average' => 62.5,
                'target' => 68.0,
                'change' => 2.4,
                'pass_rate' => 84.1,
                'tone' => 'mid',
            ],
            [
                'name' => 'Life Sciences',
                'head' => 'Dr. E. van der Merwe',
                'average' => 72.8,
                'target' => 72.0,
                'change' => 5.1,
                'pass_rate' => 96.2,
                'tone' => 'good',
            ],
            [
                'name' => 'Accounting',
                'head' => 'Mrs. M. Zulu',
                'average' => 68.4,
                'target' => 70.0,
                'change' => 4.0,
                'pass_rate' => 92.0,
                'tone' => 'good',
            ],
            [
                'name' => 'English First Additional Language',
                'head' => 'Ms. T. Khumalo',
                'average' => 74.6,
                'target' => 72.0,
                'change' => 3.2,
                'pass_rate' => 98.4,
                'tone' => 'good',
            ],
        ];

        $levels = [
            ['level' => 7, 'label' => 'Level 7 (80-100% · Outstanding)', 'percentage' => 13.3, 'count' => 312, 'tone' => 'good'],
            ['level' => 6, 'label' => 'Level 6 (70-79% · Meritorious)', 'percentage' => 28.5, 'count' => 667, 'tone' => 'good'],
            ['level' => 5, 'label' => 'Level 5 (60-69% · Substantial)', 'percentage' => 32.1, 'count' => 751, 'tone' => 'sky'],
            ['level' => 4, 'label' => 'Level 4 (50-59% · Adequate)', 'percentage' => 17.5, 'count' => 410, 'tone' => 'mid'],
            ['level' => 3, 'label' => 'Level 3 (40-49% · Moderate)', 'percentage' => 5.8, 'count' => 136, 'tone' => 'mid'],
            ['level' => 2, 'label' => 'Level 2 (30-39% · Elementary)', 'percentage' => 2.1, 'count' => 49, 'tone' => 'low'],
            ['level' => 1, 'label' => 'Level 1 (0-29% · Not Achieved)', 'percentage' => 0.7, 'count' => 16, 'tone' => 'low'],
        ];

        $trendPoints = [63.6, 65.2, 66.5, 67.8];

        $interventions = [
            [
                'title' => 'Grade 12 Euclidean Geometry Intervention',
                'cohort' => 'Grade 12A & 12B',
                'status' => 'In Progress',
                'impact' => '42 learners enrolled in Saturday revision clinic',
            ],
            [
                'title' => 'Physical Sciences Practical Skills Workshop',
                'cohort' => 'Grade 11 & 12',
                'status' => 'Scheduled',
                'impact' => 'Focusing on Stoichiometry & Newton\'s Laws',
            ],
            [
                'title' => 'Grade 10 Foundation Gateway Programme',
                'cohort' => 'Grade 10 Intake',
                'status' => 'Active',
                'impact' => 'Algebra and study methodology workshops',
            ],
        ];

        return [
            'terms' => $terms,
            'snapshot' => $snapshot,
            'cohorts' => $cohorts,
            'departments' => $departments,
            'levels' => $levels,
            'trend' => [
                'labels' => $terms,
                'points' => $trendPoints,
            ],
            'interventions' => $interventions,
        ];
    }
}
