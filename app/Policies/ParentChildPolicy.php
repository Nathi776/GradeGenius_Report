<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ParentChildPolicy
{
    /**
     * Determine whether the parent can view the child's study planner or dashboard.
     */
    public function viewPlanner(User $parent, User $child): bool
    {
        if ($parent->role !== 'parent') {
            return false;
        }

        return DB::table('parent_student_links')
            ->where('parent_id', $parent->id)
            ->where('student_id', $child->id)
            ->where('status', 'verified')
            ->exists();
    }

    /**
     * Determine whether the parent can send encouragement to the child.
     */
    public function sendEncouragement(User $parent, User $child): bool
    {
        return $this->viewPlanner($parent, $child);
    }
}
