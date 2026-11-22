<?php

namespace App\Models;

use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    /** @use HasFactory<SchoolClassFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'subject',
        'grade',
        'target',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_learner', 'school_class_id', 'learner_id')
            ->withTimestamps();
    }
}
