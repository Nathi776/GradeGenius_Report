<?php

namespace App\Models;

use Database\Factories\AssessmentResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A recorded quiz or past-paper mark for a learner in a subject.
 */
#[Fillable(['learner_subject_id', 'recorded_by', 'kind', 'title', 'score', 'attempts', 'term', 'taken_on'])]
class AssessmentResult extends Model
{
    /** @use HasFactory<AssessmentResultFactory> */
    use HasFactory;

    public const KIND_QUIZ = 'quiz';

    public const KIND_PAST_PAPER = 'past_paper';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'score' => 'integer',
            'term' => 'integer',
        ];
    }

    public function learnerSubject(): BelongsTo
    {
        return $this->belongsTo(LearnerSubject::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
