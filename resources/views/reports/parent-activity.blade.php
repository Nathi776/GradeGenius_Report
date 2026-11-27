@extends('layouts.parent', ['title' => 'Learner activity', 'active' => 'activity'])

@php
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
    $activeChild = $selectedChild?->id;
@endphp

@section('parent-content')
    <div class="space-y-5">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight text-ink">Learner activity</h1>
            <p class="mt-1 text-sm text-muted">When your child signed in and which dashboard or report pages they opened. This is shown only if they choose to share it.</p>
        </header>

        @if (count($children) > 1)
            <nav aria-label="Switch child" class="flex flex-wrap gap-2">
                @foreach ($children as $childId => $childName)
                    @php($isSelected = (int) $childId === $activeChild)
                    <a href="{{ route('parent.activity', ['child' => $childId]) }}" @if ($isSelected) aria-current="true" @endif
                        class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-blue {{ $isSelected ? 'border-brand/50 bg-brand/15 text-ink' : 'border-line bg-card-2 text-muted hover:text-ink' }}">{{ $childName }}</a>
                @endforeach
            </nav>
        @endif

        @if ($learner === null)
            <section class="{{ $card }}">
                <p class="text-sm text-muted">No confirmed child is linked to your account yet. Confirm a link request on your dashboard to see activity here.</p>
            </section>
        @elseif (! $learner['sharing'])
            <section class="{{ $card }}">
                <h2 class="text-lg font-normal text-ink">{{ $learner['name'] }}</h2>
                <p class="mt-2 text-sm text-ink">{{ explode(' ', trim($learner['name']))[0] }} has not shared activity with you.</p>
                <p class="mt-1 text-sm text-muted">Learners decide this in their own Settings. Their marks and reports are still shown on your dashboard.</p>
            </section>
        @else
            <section aria-labelledby="activity-summary-heading" class="{{ $card }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="activity-summary-heading" class="text-xl font-semibold text-ink">{{ $learner['name'] }}</h2>
                    <span class="rounded-full border border-line bg-card-2 px-3 py-1 text-sm font-semibold text-ink">{{ $learner['status_label'] }}</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
                    <x-stat-tile label="Logins, last 7 days" :value="$learner['logins_7d']" sub="Recorded portal sessions" />
                    <x-stat-tile label="Time signed in" :value="$learner['duration_7d']" sub="Across sessions in the last 7 days · not study time" tone="sky" />
                    <x-stat-tile label="Pages opened" :value="$learner['recorded_visits']" sub="Dashboard and report views, 90 days" tone="peach" />
                    <x-stat-tile label="Verified study time" :value="$learner['study']['total_minutes'].' min'" sub="Completed planner sessions" tone="good" />
                    <x-stat-tile label="Study streak" :value="$learner['study']['streak'].' '.($learner['study']['streak'] === 1 ? 'day' : 'days')" sub="Consecutive days with study sessions" tone="peach" />
                </div>
            </section>

            <section aria-labelledby="sessions-heading" class="{{ $card }}">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 id="sessions-heading" class="text-xl font-semibold text-ink">Recent sessions</h2>
                    <span class="rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted">90-day retention · no IP or location</span>
                </div>
                @if (count($learner['sessions']) > 0)
                    <div class="mt-4 space-y-3">
                        @foreach ($learner['sessions'] as $session)
                            <section class="overflow-hidden rounded-xl border border-line bg-card-2">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line p-3">
                                    <div>
                                        <p class="text-sm font-semibold text-ink">{{ $session['date_label'] }} · Login {{ $session['login_label'] }} · {{ $session['end_label'] }}</p>
                                        <p class="mt-1 text-xs text-muted">{{ $session['browser'] }} · {{ $session['platform'] }} · {{ $session['duration_label'] }} signed in</p>
                                    </div>
                                    <span class="rounded-full border border-line bg-card px-2 py-0.5 text-xs font-semibold text-ink">{{ $session['status_label'] }}</span>
                                </div>
                                @if (count($session['events']) > 0)
                                    <ul class="divide-y divide-line/70 px-3">
                                        @foreach ($session['events'] as $event)
                                            <li class="flex items-center gap-3 py-2.5 text-sm">
                                                <time class="shrink-0 rounded-md border border-line bg-card px-2 py-0.5 font-mono text-xs text-ink">{{ $event['time'] }}</time>
                                                <span class="text-ink">{{ $event['label'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="p-3 text-xs text-muted">No dashboard or report visits were recorded during this session.</p>
                                @endif
                            </section>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-muted">Time signed in runs from login to logout or the last recorded request; it is not study time.</p>
                @else
                    <p class="mt-4 text-sm text-muted">No sessions have been recorded in the last 90 days.</p>
                @endif
            </section>
        @endif
    </div>
@endsection
