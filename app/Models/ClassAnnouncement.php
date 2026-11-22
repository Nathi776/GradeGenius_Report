<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassAnnouncement extends Model
{
    use HasFactory;

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_IMPORTANT = 'important';

    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'school_class_id',
        'teacher_id',
        'title',
        'body',
        'priority',
        'recipients_count',
    ];

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
