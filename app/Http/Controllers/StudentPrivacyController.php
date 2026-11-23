<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentPrivacyController extends Controller
{
    /**
     * Let a learner choose whether parents and teachers can see their login activity.
     */
    public function updateActivitySharing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shares_activity' => ['required', 'boolean'],
        ]);

        $request->user()->forceFill(['shares_activity' => $validated['shares_activity']])->save();

        return back()->with('status', $validated['shares_activity']
            ? 'Your parents and teachers can now see when you are online.'
            : 'Your login activity is now private. Parents and teachers can no longer see it.');
    }
}
