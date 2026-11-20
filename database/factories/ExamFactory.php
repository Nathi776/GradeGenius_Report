<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_key' => 'mathematics',
            'grade' => 12,
            'title' => 'Mathematics Test',
            'paper' => 'Paper 1',
            'exam_date' => now()->addDays(fake()->numberBetween(1, 40))->toDateString(),
        ];
    }

    public function forSubject(string $subjectKey, string $title, int $daysFromToday, int $grade = 12): static
    {
        return $this->state(fn (): array => [
            'subject_key' => $subjectKey,
            'grade' => $grade,
            'title' => $title,
            'exam_date' => now()->addDays($daysFromToday)->toDateString(),
        ]);
    }
}
