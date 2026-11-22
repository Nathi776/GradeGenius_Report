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
#[Fillable([
    'user_id',
    'subject_key',
    'topic',
    'planned_date',
    'minutes',
    'status',
    'completed_at',
    'started_at',
    'finished_at',
    'actual_minutes',
    'is_counted',
    'is_paused',
    'paused_at',
    'paused_seconds',
])]
class StudySession extends Model
{
    /** @use HasFactory<StudySessionFactory> */
    use HasFactory;

    public const STATUS_PLANNED = 'planned';

    public const STATUS_DONE = 'done';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_TOO_SHORT = 'too_short';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_date' => 'date',
            'minutes' => 'integer',
            'completed_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'actual_minutes' => 'integer',
            'is_counted' => 'boolean',
            'is_paused' => 'boolean',
            'paused_at' => 'datetime',
            'paused_seconds' => 'integer',
        ];
    }

    public function minimumMinutes(): int
    {
        return (int) ceil($this->minutes * 0.8);
    }

    public function isFuture(): bool
    {
        return $this->planned_date->gt(now()->startOfDay());
    }

    public function canStartOrComplete(): bool
    {
        return ! $this->isFuture();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias relationship for user representing student.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope query to a specific student user.
     */
    public function scopeForStudent($query, int|User $student)
    {
        $id = $student instanceof User ? $student->id : $student;

        return $query->where('user_id', $id);
    }
}
