@extends('layouts.teacher', ['title' => 'New Class Announcement', 'active' => 'announcements'])

@section('teacher-content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('teacher.announcements.index') }}" class="hover:underline">Announcements</a>
        <span>/</span>
        <span class="text-ink">New</span>
    </div>

    <div class="rounded-3xl border-2 border-black bg-card p-5 sm:p-8 space-y-6">
        <div>
            <h1 class="text-xl font-bold text-ink">New class announcement</h1>
            <p class="mt-1 text-sm text-muted">Sent to the confirmed guardians of every learner in the class. Guardians get an in-app notification and an email.</p>
        </div>

        @if ($classes->isEmpty())
            <p class="rounded-xl border border-dashed border-line p-4 text-sm text-muted">You do not teach any classes yet, so there is nobody to announce to.</p>
        @else
            <form method="POST" action="{{ route('teacher.announcements.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="school_class_id" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">Class</label>
                    <select name="school_class_id" id="school_class_id" required class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm font-semibold text-ink">
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->name }} · {{ $class->subject }} ({{ $class->learners_count }} learners)</option>
                        @endforeach
                    </select>
                    @error('school_class_id')
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">Priority</label>
                    <select name="priority" id="priority" required class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm font-semibold text-ink">
                        @foreach (['normal' => 'Normal', 'important' => 'Important', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">Title</label>
                    <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}" class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink">
                    @error('title')
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">Announcement</label>
                    <textarea name="body" id="body" rows="7" required minlength="10" maxlength="3000" class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="gg-button gg-button-primary px-5 py-2.5 text-sm font-bold">Send announcement</button>
            </form>
        @endif
    </div>
</div>
@endsection
