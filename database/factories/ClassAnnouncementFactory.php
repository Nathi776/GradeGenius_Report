<?php

namespace Database\Factories;

use App\Models\ClassAnnouncement;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassAnnouncement>
 */
class ClassAnnouncementFactory extends Factory
{
    protected $model = ClassAnnouncement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schoolClass = SchoolClass::factory()->create();

        return [
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $schoolClass->teacher_id,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'priority' => ClassAnnouncement::PRIORITY_NORMAL,
            'recipients_count' => 0,
        ];
    }
}
