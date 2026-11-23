<?php

namespace Database\Factories;

use App\Models\LearnerSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerSubject>
 */
class LearnerSubjectFactory extends Factory
{
    protected $model = LearnerSubject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'name' => 'Mathematics',
            'target' => 70,
            'term_averages' => [60, 64, 68, 72],
            'questions_attempted' => 100,
            'questions_correct' => 70,
        ];
    }
}
