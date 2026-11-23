@extends('layouts.teacher', ['title' => 'Record a mark', 'active' => 'classes'])

@section('teacher-content')
@php
    $input = 'w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink';
    $label = 'block text-xs font-bold uppercase tracking-wider text-ink mb-1.5';
@endphp
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.classes.index') }}" class="hover:underline">My Classes</a>
        <span>/</span>
        <a href="{{ route('teacher.classes.show', $schoolClass->id) }}" class="hover:underline">{{ $schoolClass->name }}</a>
        <span>/</span>
        <span class="text-ink">Record a mark</span>
    </div>

    <div class="rounded-3xl border-2 border-black bg-card p-5 sm:p-8 space-y-6">
        <div>
            <h1 class="text-xl font-bold text-ink">Record a mark for {{ $student->name }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $schoolClass->subject }} · This feeds the learner's report, the parent's report and your class averages.</p>
        </div>

        <form method="POST" action="{{ route('teacher.classes.learners.results.store', [$schoolClass->id, $student->id]) }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="kind" class="{{ $label }}">Type</label>
                    <select name="kind" id="kind" required class="{{ $input }}">
                        <option value="quiz" @selected(old('kind', 'quiz') === 'quiz')>Quiz or test</option>
                        <option value="past_paper" @selected(old('kind') === 'past_paper')>Past paper</option>
                    </select>
                    @error('kind') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="term" class="{{ $label }}">Term</label>
                    <select name="term" id="term" required class="{{ $input }}">
                        @foreach ($terms as $index => $termName)
                            <option value="{{ $index + 1 }}" @selected((int) old('term', count($terms)) === $index + 1)>{{ $termName }}</option>
                        @endforeach
                    </select>
                    @error('term') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="title" class="{{ $label }}">Title</label>
                <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}" placeholder="e.g. Algebra Quiz" class="{{ $input }}">
                @error('title') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="score" class="{{ $label }}">Mark (%)</label>
                    <input type="number" name="score" id="score" required min="0" max="100" value="{{ old('score') }}" class="{{ $input }}">
                    @error('score') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="taken_on" class="{{ $label }}">Date</label>
                    <input type="date" name="taken_on" id="taken_on" required max="{{ now()->toDateString() }}" value="{{ old('taken_on', now()->toDateString()) }}" class="{{ $input }}">
                    @error('taken_on') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="attempts" class="{{ $label }}">Attempts (quiz)</label>
                    <input type="number" name="attempts" id="attempts" min="1" max="255" value="{{ old('attempts', 1) }}" class="{{ $input }}">
                    @error('attempts') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                </div>
            </div>

            <fieldset class="space-y-4 rounded-2xl border border-line p-4">
                <legend class="px-2 text-xs font-bold uppercase tracking-wider text-muted">Optional: topic and questions</legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="topic" class="{{ $label }}">Topic</label>
                        <input type="text" name="topic" id="topic" maxlength="120" value="{{ old('topic') }}" placeholder="e.g. Probability" class="{{ $input }}">
                        @error('topic') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="mastery" class="{{ $label }}">Topic mastery (%)</label>
                        <input type="number" name="mastery" id="mastery" min="0" max="100" value="{{ old('mastery') }}" class="{{ $input }}">
                        @error('mastery') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="questions_attempted" class="{{ $label }}">Questions attempted</label>
                        <input type="number" name="questions_attempted" id="questions_attempted" min="0" value="{{ old('questions_attempted') }}" class="{{ $input }}">
                        @error('questions_attempted') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="questions_correct" class="{{ $label }}">Questions correct</label>
                        <input type="number" name="questions_correct" id="questions_correct" min="0" value="{{ old('questions_correct') }}" class="{{ $input }}">
                        @error('questions_correct') <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="gg-button gg-button-primary px-5 py-2.5 text-sm font-bold">Save mark</button>
        </form>
    </div>
</div>
@endsection
