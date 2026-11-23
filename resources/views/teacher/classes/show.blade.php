@extends('layouts.teacher', ['title' => $schoolClass->name, 'active' => 'classes'])

@section('teacher-content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('teacher.classes.index') }}" class="hover:underline">My Classes</a>
        <span>/</span>
        <span class="text-ink">{{ $schoolClass->name }}</span>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">{{ session('status') }}</div>
    @endif

    <header class="rounded-3xl border-2 border-black bg-card p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Grade {{ $schoolClass->grade }} · {{ $schoolClass->subject }}@if ($schoolClass->target) · Target {{ $schoolClass->target }}%@endif</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $schoolClass->name }}</h1>
        </div>
        <a href="{{ route('teacher.announcements.create') }}" class="gg-button gg-button-surface px-4 py-2 text-xs font-bold">Announce to class</a>
    </header>

    <section class="rounded-2xl border border-line bg-card p-5 sm:p-6 space-y-3" aria-labelledby="add-heading">
        <h2 id="add-heading" class="text-lg font-semibold text-ink">Add a learner</h2>
        <p class="text-sm text-muted">Enter the email address of the learner's GradeGenius account.</p>
        <form method="POST" action="{{ route('teacher.classes.learners.store', $schoolClass->id) }}" class="flex flex-wrap items-start gap-3">
            @csrf
            <div class="min-w-0 flex-1">
                <label for="email" class="sr-only">Learner email</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="learner@example.com" class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink">
                @error('email')
                    <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="gg-button gg-button-primary px-4 py-2.5 text-sm font-bold">Add</button>
        </form>
    </section>

    <section class="rounded-2xl border border-line bg-card p-5 sm:p-6" aria-labelledby="learners-heading">
        <h2 id="learners-heading" class="text-lg font-semibold text-ink">Learners ({{ $schoolClass->learners->count() }})</h2>
        <ul class="mt-3 divide-y divide-line/70">
            @forelse ($schoolClass->learners as $learner)
                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div class="min-w-0">
                        <p class="font-medium text-ink">{{ $learner->name }}</p>
                        <p class="text-xs text-muted">{{ $learner->email }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('teacher.messages.create', ['student_id' => $learner->id, 'subject' => $schoolClass->subject, 'target' => $schoolClass->target ?? 65]) }}" class="gg-button gg-button-surface px-3 py-1.5 text-xs font-bold">Message parent</a>
                        <form method="POST" action="{{ route('teacher.classes.learners.destroy', [$schoolClass->id, $learner->id]) }}" onsubmit="return confirm('Remove {{ addslashes($learner->name) }} from this class?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="gg-button gg-button-surface px-3 py-1.5 text-xs font-bold text-bad">Remove</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="py-4 text-sm text-muted">No learners yet.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
