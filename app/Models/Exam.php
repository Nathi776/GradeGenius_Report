<?php

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A scheduled assessment for every learner of a grade taking a subject. `subject_key` is the same
 * slug the student report uses for a subject (for example "mathematics").
 */
#[Fillable(['subject_key', 'grade', 'title', 'paper', 'exam_date'])]
class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade' => 'integer',
            'exam_date' => 'date',
        ];
    }
}
