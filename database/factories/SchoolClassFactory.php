<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->create(['role' => 'teacher']),
            'name' => 'Grade 12'.$this->faker->randomElement(['A', 'B', 'C']),
            'subject' => $this->faker->randomElement(['Mathematics', 'Physical Sciences', 'Accounting', 'Life Sciences']),
            'grade' => 12,
            'target' => 65,
        ];
    }
}
