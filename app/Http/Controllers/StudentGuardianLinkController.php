<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentGuardianLinkController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_email' => [
                'required',
                'email',
                Rule::exists('users', 'email')
                    ->where('role', 'parent')
                    ->whereNotNull('email_verified_at'),
            ],
        ]);

        $parent = User::where('email', $validated['parent_email'])
            ->where('role', 'parent')
            ->whereNotNull('email_verified_at')
            ->firstOrFail();

        $request->user()->parents()->syncWithoutDetaching([
            $parent->id => [
                'linked_at' => now(),
                'status' => 'pending',
            ],
        ]);

        return back()->with('status', 'Your guardian link request has been submitted and is awaiting parent confirmation.');
    }
}
