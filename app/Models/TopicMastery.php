<?php

namespace App\Models;

use Database\Factories\TopicMasteryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * How well a learner knows one topic of a subject, as a percentage.
 */
#[Fillable(['learner_subject_id', 'topic', 'mastery'])]
class TopicMastery extends Model
{
    /** @use HasFactory<TopicMasteryFactory> */
    use HasFactory;

    public function learnerSubject(): BelongsTo
    {
        return $this->belongsTo(LearnerSubject::class);
    }
}
