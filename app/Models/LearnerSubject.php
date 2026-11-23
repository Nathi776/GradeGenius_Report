<?php

namespace App\Models;

use Database\Factories\LearnerSubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One subject a learner is recorded in: the target, the per-term averages and the question totals.
 */
#[Fillable(['student_id', 'name', 'target', 'term_averages', 'questions_attempted', 'questions_correct'])]
class LearnerSubject extends Model
{
    /** @use HasFactory<LearnerSubjectFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'term_averages' => 'array',
            'target' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function topicMasteries(): HasMany
    {
        return $this->hasMany(TopicMastery::class);
    }

    public function assessmentResults(): HasMany
    {
        return $this->hasMany(AssessmentResult::class);
    }
}
