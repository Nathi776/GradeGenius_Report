<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Parents linked to this student account.
     *
     * @return BelongsToMany<User, $this>
     */
    /**
     * The subjects this learner has recorded marks in.
     */
    public function learnerSubjects(): HasMany
    {
        return $this->hasMany(LearnerSubject::class, 'student_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'parent_student_links', 'student_id', 'parent_id')
            ->withPivot(['linked_at', 'status'])
            ->withTimestamps();
    }

    /**
     * Student accounts linked to this parent account.
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'parent_student_links', 'parent_id', 'student_id')
            ->withPivot(['linked_at', 'status'])
            ->withTimestamps();
    }

    /**
     * Verified child accounts linked to this parent account.
     *
     * @return BelongsToMany<User, $this>
     */
    public function linkedChildren(): BelongsToMany
    {
        return $this->students()->wherePivot('status', 'verified');
    }

    /**
     * Pending child account link requests awaiting parent confirmation.
     *
     * @return BelongsToMany<User, $this>
     */
    public function pendingChildren(): BelongsToMany
    {
        return $this->students()->wherePivot('status', 'pending');
    }

    /**
     * Scope query to verified parent-child links.
     */
    public function scopeVerified($query)
    {
        return $query->where('parent_student_links.status', 'verified');
    }

    /**
     * Words of encouragement sent by this parent.
     *
     * @return HasMany<ParentEncouragement, $this>
     */
    public function sentEncouragements(): HasMany
    {
        return $this->hasMany(ParentEncouragement::class, 'parent_id');
    }

    /**
     * Words of encouragement received by this student.
     *
     * @return HasMany<ParentEncouragement, $this>
     */
    public function receivedEncouragements(): HasMany
    {
        return $this->hasMany(ParentEncouragement::class, 'student_id');
    }

    /**
     * Revision sessions this learner has planned.
     *
     * @return HasMany<StudySession, $this>
     */
    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    /**
     * Classes taught by this educator.
     *
     * @return HasMany<SchoolClass, $this>
     */
    public function teachingClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    /**
     * Classes this student is enrolled in.
     *
     * @return BelongsToMany<SchoolClass, $this>
     */
    public function enrolledClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_learner', 'learner_id', 'school_class_id')
            ->withTimestamps();
    }

    /**
     * Check if this educator teaches the given student in any class.
     */
    public function teachesStudent(User|int $student): bool
    {
        $studentId = $student instanceof User ? $student->id : $student;

        return SchoolClass::query()
            ->where('teacher_id', $this->id)
            ->whereHas('learners', fn ($query) => $query->where('users.id', $studentId))
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
