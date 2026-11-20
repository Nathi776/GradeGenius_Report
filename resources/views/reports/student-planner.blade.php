@extends('layouts.student', ['title' => 'Study Planner', 'active' => 'planner'])

@php
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $button = 'inline-flex items-center rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-blue focus-visible:outline-2 focus-visible:outline-blue';
@endphp

@section('student-content')
    <div class="space-y-5">
        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-3xl font-normal tracking-tight text-ink sm:text-4xl">Study planner</h1>
                <p class="mt-2 text-muted">Grade {{ $student['grade'] }} · Your revision sessions for the week ahead</p>
            </div>
            <form method="POST" action="{{ route('student.planner.generate') }}">
                @csrf
                <button type="submit" class="{{ $button }}">{{ count($days) > 0 ? 'Rebuild my plan' : 'Build my plan' }}</button>
            </form>
        </header>

        @if (session('status'))
            <p role="status" class="rounded-xl border border-line bg-card-2 px-4 py-3 text-sm text-ink">{{ session('status') }}</p>
        @endif

        <section aria-label="Plan summary" class="grid grid-cols-3 gap-3">
            <x-stat-tile label="Sessions to do" :value="$summary['planned']" sub="From today onwards" />
            <x-stat-tile label="Done this week" :value="$summary['done_this_week']" tone="good" sub="Sessions ticked off" />
            <x-stat-tile label="Minutes planned" :value="$summary['minutes_planned']" sub="Still to do" />
        </section>

        <section aria-labelledby="plan-heading" class="{{ $card }}">
            <h2 id="plan-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                Your plan
            </h2>
            <p class="mt-1 text-sm text-muted">
                Built from your <a href="{{ route('student.recommendations') }}" class="underline hover:text-ink">learning recommendations</a>:
                highest priority first, {{ $rules['session_minutes'] }}-minute sessions, up to {{ $rules['daily_minutes'] }} minutes a day over {{ $rules['plan_days'] }} days.
                Topics are planned before the exam for that subject. Rebuilding keeps sessions you already did or skipped.
            </p>

            @forelse ($days as $day)
                <div class="mt-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wider {{ $day['is_today'] ? 'text-ink' : 'text-muted' }}">{{ $day['label'] }}</h3>
                    <ul class="mt-2 space-y-2">
                        @foreach ($day['sessions'] as $session)
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-card-2 p-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-ink {{ $session['status'] !== 'planned' ? 'line-through opacity-70' : '' }}">{{ $session['topic'] }}</p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ $session['subject'] }} · {{ $session['minutes'] }} min
                                        @if ($session['status'] === 'done') · Done @elseif ($session['status'] === 'skipped') · Skipped @endif
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($session['status'] === 'planned')
                                        <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="done">
                                            <button type="submit" class="{{ $button }}">Done</button>
                                        </form>
                                        <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="skipped">
                                            <button type="submit" class="{{ $button }}">Skip</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="planned">
                                            <button type="submit" class="{{ $button }}">Undo</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('student.planner.destroy', $session['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="{{ $button }}" aria-label="Remove {{ $session['topic'] }} session">Remove</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">You have no sessions planned yet. Choose "Build my plan" and the weakest topics are spread over the next {{ $rules['plan_days'] }} days.</p>
            @endforelse
        </section>
    </div>
@endsection
