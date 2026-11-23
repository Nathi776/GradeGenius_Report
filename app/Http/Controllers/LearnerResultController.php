<?php

namespace App\Http\Controllers;

use App\Models\AssessmentResult;
use App\Services\LearnerReportRepository;
use App\Services\LearnerResultRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LearnerResultController extends Controller
{
    /**
     * Show the form for recording a quiz or past-paper mark for a learner in the educator's class.
     */
    public function create(Request $request, int $class, int $learner): View
    {
        $schoolClass = $request->user()->teachingClasses()->findOrFail($class);
        $student = $schoolClass->learners()->where('users.id', $learner)->firstOrFail();

        return view('teacher.classes.record-result', [
            'schoolClass' => $schoolClass,
            'student' => $student,
            'terms' => LearnerReportRepository::TERMS,
        ]);
    }

    /**
     * Record the mark and update the learner's term averages.
     */
    public function store(Request $request, LearnerResultRecorder $recorder, int $class, int $learner): RedirectResponse
    {
        $schoolClass = $request->user()->teachingClasses()->findOrFail($class);
        $student = $schoolClass->learners()->where('users.id', $learner)->firstOrFail();

        $validated = $request->validate([
            'kind' => ['required', Rule::in([AssessmentResult::KIND_QUIZ, AssessmentResult::KIND_PAST_PAPER])],
            'title' => ['required', 'string', 'max:255'],
            'score' => ['required', 'integer', 'between:0,100'],
            'term' => ['required', 'integer', 'between:1,'.count(LearnerReportRepository::TERMS)],
            'taken_on' => ['required', 'date', 'before_or_equal:today'],
            'attempts' => ['nullable', 'integer', 'between:1,255'],
            'topic' => ['nullable', 'string', 'max:120', 'required_with:mastery'],
            'mastery' => ['nullable', 'integer', 'between:0,100', 'required_with:topic'],
            'questions_attempted' => ['nullable', 'integer', 'between:0,10000'],
            'questions_correct' => ['nullable', 'integer', 'min:0', 'lte:questions_attempted'],
        ]);

        $recorder->record($request->user(), $student, $schoolClass, $validated);

        return redirect()->route('teacher.classes.show', $schoolClass->id)
            ->with('status', "Recorded {$validated['score']}% for {$student->name} in {$schoolClass->subject}.");
    }
}
