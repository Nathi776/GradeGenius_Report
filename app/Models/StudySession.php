<?php

namespace App\Models;

use Database\Factories\StudySessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One planned block of revision for a learner: a topic on a day for a number of minutes.
 */
#[Fillable(['user_id', 'subject_key', 'topic', 'planned_date', 'minutes', 'status', 'completed_at'])]
class StudySession extends Model
{
    /** @use HasFactory<StudySessionFactory> */
    use HasFactory;

    public const STATUS_PLANNED = 'planned';

    public const STATUS_DONE = 'done';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_date' => 'date',
            'minutes' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
