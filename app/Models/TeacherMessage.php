<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherMessage extends Model
{
    use HasFactory;

    public const TYPE_PROGRESS_UPDATE = 'progress_update';

    public const TYPE_CONCERN = 'concern';

    public const TYPE_PRAISE = 'praise';

    public const TYPE_MEETING_REQUEST = 'meeting_request';

    public const TYPES = [
        self::TYPE_PROGRESS_UPDATE => 'Progress Update',
        self::TYPE_CONCERN => 'Academic Concern',
        self::TYPE_PRAISE => 'Praise & Commendation',
        self::TYPE_MEETING_REQUEST => 'Meeting Request',
    ];

    protected $fillable = [
        'teacher_id',
        'student_id',
        'parent_id',
        'type',
        'subject',
        'body',
        'quick_reply',
        'read_at',
        'acknowledged_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }
}
