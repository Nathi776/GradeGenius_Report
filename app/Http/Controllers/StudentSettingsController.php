<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentSettingsController extends Controller
{
    /**
     * Account and privacy settings: who may see this learner's activity and which parents are linked.
     */
    public function __invoke(Request $request): View
    {
        $guardians = $request->user()->parents()
            ->where('role', 'parent')
            ->get(['users.name', 'users.email'])
            ->map(fn ($parent): array => [
                'name' => $parent->name,
                'email' => $parent->email,
                'verified' => $parent->pivot->status === 'verified',
            ])
            ->all();

        return view('reports.student-settings', [
            'guardians' => $guardians,
            'sharesActivity' => (bool) $request->user()->shares_activity,
        ]);
    }
}
