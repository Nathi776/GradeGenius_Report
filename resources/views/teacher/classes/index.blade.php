@extends('layouts.teacher', ['title' => 'My Classes', 'active' => 'classes'])

@section('teacher-content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <span class="text-ink">My Classes</span>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">{{ session('status') }}</div>
    @endif

    <header class="rounded-3xl border-2 border-black bg-card p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">My classes</h1>
            <p class="mt-1 text-sm text-muted">Classes decide which learners you can message guardians about.</p>
        </div>
        <a href="{{ route('teacher.classes.create') }}" class="gg-button gg-button-primary px-4 py-2 text-xs font-bold">New class</a>
    </header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($classes as $class)
            <a href="{{ route('teacher.classes.show', $class) }}" class="rounded-2xl border border-line bg-card p-5 hover:border-blue">
                <p class="text-xs font-bold uppercase tracking-wider text-muted">Grade {{ $class->grade }} · {{ $class->subject }}</p>
                <h2 class="mt-1 text-lg font-semibold text-ink">{{ $class->name }}</h2>
                <p class="mt-2 text-sm text-muted">{{ $class->learners_count }} {{ \Illuminate\Support\Str::plural('learner', $class->learners_count) }}@if ($class->target) · Target {{ $class->target }}%@endif</p>
            </a>
        @empty
            <p class="rounded-2xl border border-dashed border-line p-6 text-sm text-muted sm:col-span-2 lg:col-span-3">You have no classes yet. Create one, then add your learners.</p>
        @endforelse
    </div>
</div>
@endsection
