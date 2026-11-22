<?php

namespace Database\Factories;

use App\Models\StudySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudySession>
 */
class StudySessionFactory extends Factory
{
    protected $model = StudySession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject_key' => 'mathematics',
            'topic' => 'Probability',
            'planned_date' => now()->toDateString(),
            'minutes' => 30,
            'status' => StudySession::STATUS_PLANNED,
            'completed_at' => null,
        ];
    }

    public function done(): static
    {
        return $this->state(fn (): array => [
            'status' => StudySession::STATUS_DONE,
            'completed_at' => now(),
            'started_at' => now()->subMinutes(30),
            'finished_at' => now(),
            'actual_minutes' => 30,
            'is_counted' => true,
        ]);
    }

    public function tooShort(): static
    {
        return $this->state(fn (): array => [
            'status' => StudySession::STATUS_TOO_SHORT,
            'completed_at' => null,
            'started_at' => now()->subMinutes(2),
            'finished_at' => now(),
            'actual_minutes' => 2,
            'is_counted' => false,
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn (): array => [
            'status' => StudySession::STATUS_SKIPPED,
            'completed_at' => null,
            'is_counted' => false,
        ]);
    }
}
