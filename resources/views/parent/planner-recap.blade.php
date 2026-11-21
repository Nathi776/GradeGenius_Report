@extends('layouts.parent', ['title' => 'Weekly Study Recap · '.$selectedChild->name, 'active' => 'planner'])

@php
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
@endphp

@section('parent-content')
    <style media="print">
        @page {
            margin: 1.5cm;
            size: portrait;
        }
        aside, .no-print, nav, button, .recap-nav-btn {
            display: none !important;
        }
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .portal-workspace {
            padding: 0 !important;
            max-width: 100% !important;
        }
        main {
            width: 100% !important;
        }
        .gg-brutal-card {
            box-shadow: none !important;
            border-color: #000 !important;
        }
    </style>

    <div class="space-y-5">
        {{-- Navigation & Print Bar --}}
        <div class="no-print flex flex-wrap items-center justify-between gap-3 rounded-2xl border-2 border-black bg-card p-4">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('parent.planner', ['child_id' => $selectedChild->id]) }}"
                    class="gg-brutal-card inline-flex items-center gap-1.5 rounded-xl border-2 border-black bg-card-2 px-3 py-1.5 text-xs font-semibold text-ink transition hover:-translate-y-0.5">
                    ← Back to Study Planner
                </a>
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Learner:</span>
                @foreach ($children as $c)
                    @php($isActive = $selectedChild->id === $c->id)
                    <a href="{{ route('parent.planner.recap', ['child_id' => $c->id, 'week' => $weekStart->toDateString()]) }}"
                        class="rounded-xl border-2 px-3 py-1 text-xs font-semibold transition {{ $isActive ? 'border-black bg-butter text-ink shadow-xs' : 'border-line bg-card-2 text-muted hover:text-ink' }}">
                        {{ $c->name }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('parent.planner.recap', ['child_id' => $selectedChild->id, 'week' => $prevWeek]) }}"
                    class="recap-nav-btn inline-flex items-center rounded-xl border border-line bg-card-2 px-3 py-1.5 text-xs font-medium text-ink hover:border-black">
                    ← Previous Week
                </a>
                <a href="{{ route('parent.planner.recap', ['child_id' => $selectedChild->id, 'week' => $nextWeek]) }}"
                    class="recap-nav-btn inline-flex items-center rounded-xl border border-line bg-card-2 px-3 py-1.5 text-xs font-medium text-ink hover:border-black">
                    Next Week →
                </a>
                <button type="button" onclick="window.print()" class="gg-brutal-card inline-flex items-center gap-2 rounded-xl border-2 border-black bg-butter px-4 py-2 text-xs font-semibold text-ink transition hover:-translate-y-0.5">
                    @include('partials.icon', ['name' => 'printer', 'size' => 15])
                    Print / Save PDF
                </button>
            </div>
        </div>

        {{-- Weekly Header --}}
        <header class="{{ $card }} flex flex-wrap items-center justify-between gap-4 bg-butter">
            <div class="flex items-center gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white font-semibold text-2xl text-ink">
                    {{ strtoupper(substr(trim($selectedChild->name), 0, 1)) }}
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Weekly Study Performance Recap</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">{{ $selectedChild->name }}</h1>
                    <p class="mt-1 text-xs text-muted">Week of {{ $weekStart->format('D j M') }} – {{ $weekEnd->format('D j M Y') }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Plan Completion</p>
                <p class="mt-1 font-mono text-3xl font-black text-ink">{{ $completionPct }}%</p>
            </div>
        </header>

        {{-- Progress Bar --}}
        <div class="overflow-hidden rounded-2xl border-2 border-black bg-card-2 p-4">
            <div class="flex items-center justify-between text-xs font-semibold text-ink">
                <span>{{ $minutesCompleted }} minutes completed (actual)</span>
                <span>{{ $minutesPlanned }} minutes planned</span>
            </div>
            <div class="mt-2 h-4 w-full overflow-hidden rounded-full border border-line bg-line/40">
                <div class="h-full rounded-full bg-good transition-all" style="width: {{ $completionPct }}%"></div>
            </div>
        </div>

        {{-- Key Weekly Summary Tiles --}}
        <section aria-label="Key metrics" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Total Planned</p>
                <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $plannedCount }}</p>
                <p class="mt-1 text-xs text-muted">Sessions scheduled</p>
            </div>
            <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-good">Done (Counted)</p>
                <p class="mt-2 text-2xl font-bold text-good tabular-nums">{{ $doneCount }}</p>
                <p class="mt-1 text-xs text-muted">Met required duration</p>
            </div>
            <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-warn">Skipped</p>
                <p class="mt-2 text-2xl font-bold text-warn tabular-nums">{{ $skippedCount }}</p>
                <p class="mt-1 text-xs text-muted">Marked as skipped</p>
            </div>
            <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-sky">Actual Minutes</p>
                <p class="mt-2 text-2xl font-bold text-sky tabular-nums">{{ $minutesCompleted }}m</p>
                <p class="mt-1 text-xs text-muted">Of {{ $minutesPlanned }}m planned</p>
            </div>
        </section>

        {{-- Per-day Breakdown Mon-Sun --}}
        <section aria-labelledby="daily-breakdown-heading" class="{{ $card }}">
            <h2 id="daily-breakdown-heading" class="text-xl font-semibold text-ink">Daily breakdown (Mon – Sun)</h2>
            <p class="mt-1 text-sm text-muted">Overview of study sessions allocated and completed for each day of the week.</p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
                @foreach ($daysBreakdown as $day)
                    <div class="flex flex-col justify-between rounded-2xl border-2 border-black bg-card-2 p-3.5">
                        <div>
                            <div class="flex items-center justify-between border-b border-line pb-1.5">
                                <span class="font-bold text-xs uppercase text-ink">{{ substr($day['day_name'], 0, 3) }}</span>
                                <span class="text-[11px] text-muted">{{ $day['date_label'] }}</span>
                            </div>
                            <div class="mt-3 space-y-1">
                                <p class="text-xs text-ink font-semibold">{{ $day['done'] }} / {{ $day['total'] }} completed</p>
                                <p class="text-[11px] text-muted">{{ $day['minutes_done'] }} min studied</p>
                            </div>
                        </div>

                        @if (count($day['sessions']) > 0)
                            <div class="mt-3 border-t border-line/60 pt-2 space-y-1.5">
                                @foreach ($day['sessions'] as $s)
                                    <div class="truncate text-[11px]" title="{{ $s['topic'] }} ({{ $s['subject'] }})">
                                        @if ($s['status'] === 'done')
                                            <span class="text-good font-medium">✓ {{ $s['topic'] }}</span>
                                        @elseif ($s['status'] === 'skipped')
                                            <span class="text-muted">⊘ {{ $s['topic'] }}</span>
                                        @elseif ($s['status'] === 'too_short')
                                            <span class="text-bad font-medium">⚠ {{ $s['topic'] }}</span>
                                        @else
                                            <span class="text-ink">• {{ $s['topic'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-3 border-t border-line/60 pt-2 text-[11px] text-muted italic">No sessions</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Per-subject Breakdown --}}
        <section aria-labelledby="subject-breakdown-heading" class="{{ $card }}">
            <h2 id="subject-breakdown-heading" class="text-xl font-semibold text-ink">Subject breakdown</h2>
            <p class="mt-1 text-sm text-muted">Sessions and real study time logged across each subject this week.</p>

            @if (count($subjectBreakdown) > 0)
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm text-ink">
                        <thead class="border-b-2 border-black text-xs uppercase text-muted">
                            <tr>
                                <th class="py-2.5 px-3">Subject</th>
                                <th class="py-2.5 px-3">Sessions Planned</th>
                                <th class="py-2.5 px-3">Sessions Done</th>
                                <th class="py-2.5 px-3">Real Minutes Logged</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/60 font-medium">
                            @foreach ($subjectBreakdown as $row)
                                <tr>
                                    <td class="py-3 px-3 font-semibold text-ink">{{ $row['subject'] }}</td>
                                    <td class="py-3 px-3 tabular-nums">{{ $row['planned_count'] }}</td>
                                    <td class="py-3 px-3 tabular-nums text-good">{{ $row['done_count'] }}</td>
                                    <td class="py-3 px-3 tabular-nums font-bold text-sky">{{ $row['minutes_completed'] }} min</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-4 text-sm text-muted">No subject sessions were scheduled during this week.</p>
            @endif
        </section>

        {{-- Encouragements Sent This Week --}}
        <section aria-labelledby="recap-encouragements-heading" class="{{ $card }}">
            <h2 id="recap-encouragements-heading" class="text-xl font-semibold text-ink">Words of encouragement sent this week</h2>
            <p class="mt-1 text-sm text-muted">Parent notes sent to {{ $selectedChild->name }} between {{ $weekStart->format('j M') }} and {{ $weekEnd->format('j M Y') }}.</p>

            @if ($encouragements->isNotEmpty())
                <ul class="mt-4 space-y-2.5">
                    @foreach ($encouragements as $enc)
                        <li class="rounded-xl border border-line bg-card-2 p-3.5">
                            <p class="text-sm font-medium text-ink">"{{ $enc->message }}"</p>
                            <p class="mt-1 text-xs text-muted">{{ $enc->created_at->format('l j M Y · H:i') }} by {{ $enc->parent->name }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-4 text-sm text-muted">No encouragement notes were recorded during this week.</p>
            @endif
        </section>
    </div>
@endsection
