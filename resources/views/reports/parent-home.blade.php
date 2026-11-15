@extends('layouts.parent', ['title' => 'Parent dashboard', 'active' => 'home'])

@php
    $child = $dashboard['child'];
    $snap = $dashboard['snapshot'];
    $act = $dashboard['activity'];
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad', 'sky' => 'text-sky'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
    $soon = 'shrink-0 whitespace-nowrap rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted';
    $pts = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')).abs($n).' pts';
    $reportUrl = route('parent.report', ['child' => $dashboard['active_child']]);
@endphp

@section('parent-content')
    @include('partials.print-header', ['title' => 'Parent & Guardian Academic Progress Report', 'student' => $dashboard['child']])

    <div class="space-y-5">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl font-normal tracking-tight text-ink sm:text-4xl">{{ $child['first_name'] }}'s progress</h1>
                <p class="mt-2 text-muted">Grade {{ $child['grade'] }} · {{ $snap['term'] }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-ink">
                    <span class="text-muted">Subjects at goal</span>
                    <span class="font-semibold text-ink">{{ $snap['at_target'] }} of {{ $snap['subject_count'] }}</span>
                </span>
            </div>
        </header>

        <div class="grid gap-5 lg:grid-cols-12">
            {{-- 1. The answer first --}}
            <section aria-labelledby="summary-heading" class="relative overflow-hidden {{ $card }} lg:col-span-12">
                <div class="pointer-events-none absolute -right-12 -top-12 size-44 rounded-full bg-brand/25 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <h2 id="summary-heading" class="sr-only">Summary</h2>
                    <p class="text-xl leading-snug text-ink sm:text-2xl font-normal">{{ $dashboard['summary'] }}</p>
                    @if ($dashboard['next_step'])
                        <div class="mt-4 rounded-xl border border-brand/30 bg-brand/10 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-brand">Suggested next step</p>
                            <p class="mt-1 text-ink font-medium text-sm">{{ $dashboard['next_step'] }}</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- 2. Academic snapshot --}}
            <section aria-label="Academic snapshot" class="grid grid-cols-2 gap-3 lg:col-span-12 lg:grid-cols-4">
                <x-stat-tile label="Current average" :value="$snap['average'].'%'" :sub="'Level '.$snap['level'].' of 7 · '.$snap['term']" />
                <x-stat-tile label="Change" :value="$pts($snap['change'])" :sub="'Since '.$snap['first_term']" :tone="$snap['change'] > 0 ? 'good' : ($snap['change'] < 0 ? 'low' : 'default')" />
                <x-stat-tile label="Subjects at goal" :value="$snap['at_target'].' of '.$snap['subject_count']" sub="Goal set for each subject" tone="peach" />
                <x-stat-tile label="Completed recently" :value="$act['completed_30d']" sub="quizzes and past papers, last 30 days" tone="sky" />
            </section>

            {{-- 3. Progress across the terms --}}
            <section aria-labelledby="progress-heading" class="{{ $card }} lg:col-span-7">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 id="progress-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'trending-up', 'size' => 20])
                        Academic progress
                    </h2>
                    <a href="{{ $reportUrl }}" class="{{ $link }}">Full report @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
                </div>
                <p class="mt-1 text-sm text-muted">Average across all subjects, term by term. @if ($snap['goal'] !== null)Average goal: {{ $snap['goal'] }}%.@endif</p>
                <div class="mt-4">
                    <x-line-chart :series="$dashboard['progress']['series']" :labels="$dashboard['progress']['labels']" id="parent-home-progress" :height="220" />
                </div>
            </section>

            {{-- 4. Going well: lead with the positives --}}
            <section aria-labelledby="wins-heading" class="{{ $card }} lg:col-span-5">
                <h2 id="wins-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'star', 'size' => 20])
                    Going well
                </h2>
                <ul class="mt-3 space-y-4">
                    @forelse ($dashboard['wins'] as $s)
                        <li class="rounded-xl border border-line bg-card-2 p-3.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-medium text-ink">{{ $s['name'] }}</span>
                                <span class="tabular-nums font-semibold text-ink">{{ $s['current'] }}%</span>
                            </div>
                            <p class="mt-1 text-xs text-muted">
                                {{ $s['target_status']['state'] === 'met' ? 'At goal' : 'Improving' }}@if ($s['change'] !== 0) · <span class="{{ $s['change'] > 0 ? 'text-good' : 'text-bad' }}">{{ $pts($s['change']) }} since {{ $snap['first_term'] }}</span>@endif
                            </p>
                        </li>
                    @empty
                        <li class="text-sm text-muted">No subject has reached its goal yet. Small steady gains still count.</li>
                    @endforelse
                </ul>
            </section>

            {{-- 5. Learning support: each item shows the evidence it comes from --}}
            <section aria-labelledby="support-heading" class="{{ $card }} lg:col-span-7">
                <h2 id="support-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                    Learning support
                </h2>
                <p class="mt-1 text-sm text-muted">Subjects below their goal, furthest first, with the topic behind each.</p>
                @if (count($dashboard['support']) > 0)
                    <ul class="mt-4 space-y-3">
                        @foreach ($dashboard['support'] as $s)
                            <li class="rounded-xl border border-line bg-card-2 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium text-ink">{{ $s['name'] }}</span>
                                        <span class="tabular-nums text-ink font-semibold">{{ $s['current'] }}%</span>
                                    </div>
                                    <span class="text-xs font-semibold {{ $text[$s['target_status']['tone']] }}">Goal {{ $s['target'] }}% · {{ $s['target_status']['detail'] }}</span>
                                </div>
                                @if ($s['weakest'])
                                    <p class="mt-1 text-xs text-muted">Weakest topic: <span class="text-ink font-medium">{{ $s['weakest']['name'] }}</span> ({{ $s['weakest']['mastery'] }}% mastery)</p>
                                @endif
                                <p class="mt-2 text-xs text-ink-sub bg-card/60 rounded-lg p-2.5 border border-line/60">{{ $s['home_steps'][0] }}</p>
                                <a href="{{ $reportUrl }}#subject-{{ $s['key'] }}" class="mt-3 {{ $link }}">View detail<span class="sr-only"> for {{ $s['name'] }}</span> @include('partials.icon', ['name' => 'arrow-right', 'size' => 14])</a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($dashboard['support_total'] > count($dashboard['support']))
                        <p class="mt-3 text-sm text-muted">{{ $dashboard['support_total'] - count($dashboard['support']) }} more in the full report.</p>
                    @endif
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">Every subject is at or above its goal. Keep up the encouragement.</p>
                @endif
            </section>

            {{-- 6. Recent activity --}}
            <section aria-labelledby="activity-heading" class="{{ $card }} lg:col-span-5">
                <h2 id="activity-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'zap', 'size' => 20])
                    Recent activity
                </h2>
                <p class="mt-1 text-sm text-muted">Last 30 days: {{ $act['completed_30d'] }} completed · most recent {{ $act['last_ago'] ?? 'none yet' }}.</p>
                @if (count($dashboard['recent']) > 0)
                    <ul class="mt-3 divide-y divide-line/70">
                        @foreach ($dashboard['recent'] as $row)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-ink text-sm">{{ $row['title'] }}</p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $row['subject'] }} · {{ $row['type_label'] }} · {{ $row['date_label'] }}</p>
                                </div>
                                <span class="text-base font-semibold tabular-nums {{ $text[$row['tone']] }}">{{ $row['score'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-muted">Quizzes and past papers will show up here once {{ $child['first_name'] }} completes them.</p>
                @endif
            </section>

            {{-- 7. Upcoming assessments --}}
            <section aria-labelledby="exams-heading" class="{{ $card }} lg:col-span-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="exams-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 20])
                        Upcoming assessments
                    </h2>
                    <span class="{{ $soon }}">Coming soon</span>
                </div>
                <p class="mt-4 text-sm text-muted">No assessments scheduled yet. Dates will appear here once a schedule is added.</p>
            </section>

            {{-- 8. Study planner --}}
            <section aria-labelledby="planner-heading" class="{{ $card }} lg:col-span-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="planner-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                        Study planner
                    </h2>
                    <span class="{{ $soon }}">Coming soon</span>
                </div>
                <p class="mt-4 text-sm text-muted">You'll be able to see {{ $child['first_name'] }}'s planned revision sessions here. Nothing is scheduled because the planner isn't set up.</p>
            </section>

            {{-- 9. Notifications --}}
            <section aria-labelledby="notifications-heading" class="{{ $card }} lg:col-span-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="notifications-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'mail', 'size' => 20])
                        Notifications
                    </h2>
                    <span class="{{ $soon }}">Coming soon</span>
                </div>
                <p class="mt-4 text-sm text-muted">Alerts about marks and goals will appear here once notifications are switched on.</p>
            </section>
        </div>
    </div>
@endsection
