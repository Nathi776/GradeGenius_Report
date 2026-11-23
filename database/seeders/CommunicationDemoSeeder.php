<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Optional: gives the demo teacher one real class containing the demo student, so "Message Parent" can be tried end to end.
 * Run with: php artisan db:seed --class=CommunicationDemoSeeder
 * Note: once a teacher has a real class, the teacher dashboard shows that class instead of the sample roster.
 */
class CommunicationDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Demo seeders are disabled in production.');

            return;
        }

        $teacher = User::where('email', 'teacher@example.com')->firstOrFail();
        $student = User::where('email', 'test@example.com')->firstOrFail();

        $class = SchoolClass::firstOrCreate(
            ['teacher_id' => $teacher->id, 'name' => 'Grade 12 Mathematics'],
            ['subject' => 'Mathematics', 'grade' => 12, 'target' => 65],
        );
        $class->learners()->syncWithoutDetaching([$student->id]);
    }
}
