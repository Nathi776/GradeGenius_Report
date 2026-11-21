@extends('layouts.parent', ['title' => 'Study Planner · '.($selectedChild ? $selectedChild->name : 'Learner View'), 'active' => 'planner'])

@php
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
@endphp

@section('parent-content')
    <div class="space-y-5">
        {{-- Child Switcher & Page Actions (Task 3) --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border-2 border-black bg-card p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Learner:</span>
                @if ($children->isNotEmpty())
                    @foreach ($children as $c)
                        @php($isActive = $selectedChild && $selectedChild->id === $c->id)
                        <a href="{{ route('parent.planner', ['child_id' => $c->id]) }}"
                            class="inline-flex items-center gap-2 rounded-xl border-2 px-3 py-1.5 text-sm font-semibold transition {{ $isActive ? 'border-black bg-butter text-ink shadow-xs' : 'border-line bg-card-2 text-muted hover:text-ink hover:border-black' }}">
                            <span class="flex size-6 items-center justify-center rounded-lg border border-black bg-white text-xs font-bold">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
                            {{ $c->name }}
                        </a>
                    @endforeach
                @endif
                <a href="{{ route('parent.home') }}#registered-learners-heading"
                    class="inline-flex items-center gap-1 rounded-xl border border-dashed border-line px-3 py-1.5 text-xs font-semibold text-muted hover:border-black hover:text-ink">
                    + Link Another Child
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($selectedChild)
                    <a href="{{ route('parent.planner.recap', ['child_id' => $selectedChild->id]) }}" class="gg-brutal-card inline-flex items-center gap-2 rounded-xl border-2 border-black bg-card-2 px-3.5 py-2 text-xs font-semibold text-ink transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-blue">
                        @include('partials.icon', ['name' => 'bar-chart', 'size' => 15])
                        Weekly Recap
                    </a>
                @endif
                <button type="button" onclick="window.print()" class="gg-brutal-card inline-flex items-center gap-2 rounded-xl border-2 border-black bg-butter px-3.5 py-2 text-xs font-semibold text-ink transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-blue">
                    @include('partials.icon', ['name' => 'printer', 'size' => 15])
                    Print Plan
                </button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('status'))
            <p role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-700/60 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p role="alert" class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 dark:border-red-700/60 dark:bg-red-950/40 dark:text-red-300">{{ session('error') }}</p>
        @endif

        {{-- Main Content or Empty / Pending States (Task 3) --}}
        @if ($selectedChild)
            @include('parent.planner._child-plan', [
                'child' => $selectedChild,
                'days' => $days,
                'stats' => $stats,
                'encouragements' => $encouragements,
            ])
        @elseif ($hasPending)
            <div class="{{ $card }} text-center">
                <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-warn/20 text-3xl" aria-hidden="true">⏳</span>
                <h2 class="mt-4 text-xl font-bold text-ink">Waiting for verification</h2>
                <p class="mt-2 text-sm text-muted">A learner account has connected using your parent email, but verification is still pending. Once approved, the study planner will display here.</p>
            </div>
        @else
            <div class="{{ $card }} text-center">
                <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-card-2 text-3xl" aria-hidden="true">👨‍👩‍👧</span>
                <h2 class="mt-4 text-xl font-bold text-ink">No linked children yet</h2>
                <p class="mt-2 text-sm text-muted">Link a learner account from student registration or settings to view and encourage their study revision schedule.</p>
                <div class="mt-5">
                    <a href="{{ route('parent.home') }}#registered-learners-heading" class="gg-brutal-card inline-flex items-center gap-2 rounded-xl border-2 border-black bg-butter px-5 py-2.5 text-sm font-semibold text-ink">
                        Link Child
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
