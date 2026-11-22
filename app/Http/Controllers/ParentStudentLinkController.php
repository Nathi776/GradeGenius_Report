<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParentStudentLinkController extends Controller
{
    /**
     * Parent approves a pending student link request.
     */
    public function confirm(Request $request, User $student): RedirectResponse
    {
        $parent = $request->user();

        $link = $parent->students()
            ->where('users.id', $student->id)
            ->wherePivot('status', 'pending')
            ->first();

        if (! $link) {
            return back()->with('error', 'Pending link request not found.');
        }

        $parent->students()->updateExistingPivot($student->id, [
            'status' => 'verified',
            'linked_at' => now(),
        ]);

        return back()->with('status', "You have approved the link to {$student->name}. You can now view their academic reports and progress.");
    }

    /**
     * Parent declines or removes a student link request.
     */
    public function reject(Request $request, User $student): RedirectResponse
    {
        $parent = $request->user();

        $exists = $parent->students()
            ->where('users.id', $student->id)
            ->exists();

        if (! $exists) {
            return back()->with('error', 'Link request not found.');
        }

        $parent->students()->detach($student->id);

        return back()->with('status', "Link request for {$student->name} has been removed.");
    }
}
