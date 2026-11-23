<?php

namespace Database\Factories;

use App\Models\LearnerSubject;
use App\Models\TopicMastery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicMastery>
 */
class TopicMasteryFactory extends Factory
{
    protected $model = TopicMastery::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learner_subject_id' => LearnerSubject::factory(),
            'topic' => fake()->unique()->word(),
            'mastery' => fake()->numberBetween(30, 95),
        ];
    }
}
