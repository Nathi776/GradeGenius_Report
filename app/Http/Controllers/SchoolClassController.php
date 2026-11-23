<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * List the classes this educator teaches.
     */
    public function index(Request $request): View
    {
        return view('teacher.classes.index', [
            'classes' => $request->user()->teachingClasses()->withCount('learners')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a class.
     */
    public function create(): View
    {
        return view('teacher.classes.create');
    }

    /**
     * Create a class owned by the signed-in educator.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:120'],
            'grade' => ['required', 'integer', 'between:1,12'],
            'target' => ['nullable', 'integer', 'between:0,100'],
        ]);

        $schoolClass = $request->user()->teachingClasses()->create($validated);

        return redirect()->route('teacher.classes.show', $schoolClass)
            ->with('status', "{$schoolClass->name} created. Add your learners below.");
    }

    /**
     * Show a class and its learners.
     */
    public function show(Request $request, int $class): View
    {
        $schoolClass = $request->user()->teachingClasses()->with('learners')->findOrFail($class);

        return view('teacher.classes.show', ['schoolClass' => $schoolClass]);
    }

    /**
     * Enrol an existing learner account, found by email address.
     */
    public function addLearner(Request $request, int $class): RedirectResponse
    {
        $schoolClass = $request->user()->teachingClasses()->findOrFail($class);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $learner = User::query()->where('role', 'student')->where('email', $validated['email'])->first();

        if ($learner === null) {
            return back()->withInput()->withErrors(['email' => 'No learner account uses that email address.']);
        }

        if ($schoolClass->learners()->where('users.id', $learner->id)->exists()) {
            return back()->withInput()->withErrors(['email' => "{$learner->name} is already in this class."]);
        }

        $schoolClass->learners()->attach($learner->id);

        return back()->with('status', "{$learner->name} added to {$schoolClass->name}.");
    }

    /**
     * Remove a learner from a class.
     */
    public function removeLearner(Request $request, int $class, int $learner): RedirectResponse
    {
        $schoolClass = $request->user()->teachingClasses()->findOrFail($class);

        $schoolClass->learners()->detach($learner);

        return back()->with('status', 'Learner removed from the class.');
    }
}
