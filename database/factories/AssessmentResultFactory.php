<?php

namespace Database\Factories;

use App\Models\AssessmentResult;
use App\Models\LearnerSubject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentResult>
 */
class AssessmentResultFactory extends Factory
{
    protected $model = AssessmentResult::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learner_subject_id' => LearnerSubject::factory(),
            'recorded_by' => null,
            'kind' => AssessmentResult::KIND_QUIZ,
            'title' => fake()->words(3, true),
            'score' => fake()->numberBetween(40, 95),
            'attempts' => 1,
            'term' => 4,
            'taken_on' => now()->toDateString(),
        ];
    }

    public function pastPaper(): static
    {
        return $this->state(['kind' => AssessmentResult::KIND_PAST_PAPER, 'attempts' => null]);
    }
}
