@extends('layouts.app', ['title' => $report['first_name'] . "'s progress"])

@php
    $o = $report['overall'];
    $a = $report['activity'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad'];
    $dot = ['good' => 'bg-good', 'mid' => 'bg-warn', 'low' => 'bg-bad'];
    $pts = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')) . abs($n) . ' pts';
    $changeClass = fn ($n) => $n > 0 ? 'text-good' : ($n < 0 ? 'text-bad' : 'text-muted');
@endphp

@section('content')
    @include('partials.report-header')

    <div class="mx-auto w-full max-w-4xl px-4 pb-12 pt-6">
        <header>
            <h1 class="text-3xl font-normal tracking-tight sm:text-4xl">{{ $report['student']['name'] }}'s progress</h1>
            <p class="mt-2 text-muted">Grade {{ $report['student']['grade'] }} · {{ $o['current_term'] }}</p>

            @if (count($children) > 1)
                <nav aria-label="Choose a child" class="mt-4 flex flex-wrap gap-2">
                    @foreach ($children as $key => $name)
                        <a href="{{ route('parent.report', ['child' => $key]) }}" @if ($key === $activeChild) aria-current="page" @endif
                            class="rounded-full border border-line bg-card-2 px-4 py-1.5 text-sm text-muted hover:text-white aria-[current=page]:border-white/60 aria-[current=page]:text-white focus-visible:outline-2 focus-visible:outline-blue">{{ $name }}</a>
                    @endforeach
                </nav>
            @endif
        </header>

        {{-- 1. The answer first: one sentence, one suggested step --}}
        <section aria-labelledby="summary-heading" class="mt-6 {{ $card }}">
            <h2 id="summary-heading" class="sr-only">Summary</h2>
            <p class="text-xl leading-snug text-white sm:text-2xl">{{ $report['summary'] }}</p>

            @if ($report['next_step'])
                <div class="mt-4 rounded-xl border border-brand/30 bg-brand/10 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand">Suggested next step</p>
                    <p class="mt-1 text-slate-100">{{ $report['next_step'] }}</p>
                </div>
            @endif

            <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <x-stat-tile label="Current average" :value="$o['current'] . '%'" :sub="'Level ' . $o['level'] . ' of 7 · ' . $o['current_term']" />
                <x-stat-tile label="Change" :value="$pts($o['change'])" :sub="'Since ' . $o['first_term']" :tone="$o['change'] > 0 ? 'good' : ($o['change'] < 0 ? 'low' : 'default')" />
                <x-stat-tile label="Subjects at goal" :value="$o['at_target'] . ' of ' . $o['subject_count']" sub="Goal set for each subject" tone="sky" />
                <x-stat-tile label="Completed recently" :value="$a['completed_30d']" sub="quizzes and past papers, last 30 days" tone="peach" />
            </div>
        </section>

        {{-- 2. Where to help, and what is going well --}}
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <section aria-labelledby="support-heading" class="{{ $card }}">
                <h2 id="support-heading" class="text-lg font-normal">Needs support</h2>
                <ul class="mt-3 space-y-4">
                    @forelse ($report['needs_support'] as $s)
                        <li>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium text-white">{{ $s['name'] }}</span>
                                <span class="tabular-nums text-white">{{ $s['current'] }}%</span>
                                <x-level-chip :level="$s['level']" />
                            </div>
                            <p class="mt-1 text-sm {{ $text[$s['target_status']['tone']] }}">
                                Goal {{ $s['target'] }}% · {{ $s['target_status']['detail'] }}@if ($s['change'] > 0), <span class="text-good">up {{ $s['change'] }} pts since {{ $o['first_term'] }}</span>@endif
                            </p>
                            @if ($s['weakest'])
                                <p class="mt-1 text-sm text-muted">Weakest topic: <span class="text-slate-200">{{ $s['weakest']['name'] }}</span> ({{ $s['weakest']['mastery'] }}%)</p>
                            @endif
                            <a href="#subject-{{ $s['key'] }}" class="mt-1 inline-block text-sm text-sky underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-blue">See details</a>
                        </li>
                    @empty
                        <li class="text-sm text-muted">Every subject is at or above its goal.</li>
                    @endforelse
                </ul>
            </section>

            <section aria-labelledby="well-heading" class="{{ $card }}">
                <h2 id="well-heading" class="text-lg font-normal">Going well</h2>
                <ul class="mt-3 space-y-4">
                    @forelse ($report['going_well'] as $s)
                        <li>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium text-white">{{ $s['name'] }}</span>
                                <span class="tabular-nums text-white">{{ $s['current'] }}%</span>
                                <x-level-chip :level="$s['level']" />
                            </div>
                            <p class="mt-1 text-sm text-muted">
                                {{ $s['target_status']['state'] === 'met' ? 'At goal' : 'Improving' }}
                                @if ($s['change'] !== 0)· <span class="{{ $changeClass($s['change']) }}">{{ $pts($s['change']) }} since {{ $o['first_term'] }}</span>@endif
                            </p>
                        </li>
                    @empty
                        <li class="text-sm text-muted">No subject has reached its goal yet. Small steady gains still count.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- 3. One line across the terms --}}
        <section aria-labelledby="progress-heading" class="mt-6 {{ $card }}">
            <h2 id="progress-heading" class="text-lg font-normal">Average across the terms</h2>
            <div class="mt-4">
                <x-line-chart :series="[$report['chart']['series'][0]]" :labels="$report['terms']" id="parent-progress" :height="240" />
            </div>
        </section>

        {{-- 4. Subjects, collapsed so the page can be scanned quickly --}}
        <section aria-labelledby="subjects-heading" class="mt-6 {{ $card }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="subjects-heading" class="text-lg font-normal">Subject by subject</h2>
                    <p class="mt-1 text-sm text-muted">Open a subject to see what to work on and ideas for home.</p>
                </div>
                <button type="button" data-toggle-all class="rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm text-sky hover:border-blue focus-visible:outline-2 focus-visible:outline-blue">Expand all</button>
            </div>

            <div class="mt-4 space-y-3">
                @foreach ($report['subjects'] as $s)
                    @php $tone = $s['target_status']['tone']; @endphp
                    <details data-subject-card id="subject-{{ $s['key'] }}" class="group rounded-xl border border-line bg-card-2 open:border-blue/40">
                        <summary class="flex cursor-pointer list-none flex-wrap items-center gap-x-4 gap-y-2 rounded-xl p-4 focus-visible:outline-2 focus-visible:outline-blue [&::-webkit-details-marker]:hidden">
                            <span class="flex min-w-40 flex-1 items-center gap-3">
                                <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $s['color'] }}"></span>
                                <span class="font-medium text-white">{{ $s['name'] }}</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="text-xl font-semibold tabular-nums text-white">{{ $s['current'] }}%</span>
                                <x-level-chip :level="$s['level']" />
                                <span class="text-sm font-medium {{ $changeClass($s['change']) }}">{{ $pts($s['change']) }}<span class="sr-only"> since {{ $o['first_term'] }}</span></span>
                            </span>
                            <span class="flex items-center gap-2 text-sm {{ $text[$tone] }}">
                                <span class="size-2 rounded-full {{ $dot[$tone] }}" aria-hidden="true"></span>{{ $s['status_label'] }}
                            </span>
                            <svg class="ml-auto size-4 shrink-0 text-muted transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>

                        <div class="grid gap-6 border-t border-line p-4 sm:p-5 lg:grid-cols-2">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Topics to work on</h3>
                                    @if ($s['attention'])
                                        <div class="mt-3 space-y-3">
                                            @foreach ($s['attention'] as $t)
                                                <x-bar-row :label="$t['name']" :value="$t['mastery']" :tone="$t['tone']" />
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-2 text-sm text-muted">No topic is below 65%.</p>
                                    @endif
                                    @if ($s['strongest'])
                                        <p class="mt-4 text-sm text-good"><span aria-hidden="true">✓</span> Doing well in: {{ implode(', ', array_column($s['strongest'], 'name')) }}</p>
                                    @endif
                                </div>

                                <div>
                                    <h3 class="text-sm font-semibold text-white">Ideas for home</h3>
                                    <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm text-slate-200">
                                        @foreach ($s['home_steps'] as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Latest results</h3>
                                    <ul class="mt-2 divide-y divide-line/70 text-sm">
                                        @foreach ($s['recent'] as $r)
                                            <li class="flex items-center justify-between gap-3 py-2">
                                                <span class="min-w-0">
                                                    <span class="block truncate text-white">{{ $r['title'] }}</span>
                                                    <span class="text-xs text-muted">{{ $r['type_label'] }} · {{ $r['date_label'] }}</span>
                                                </span>
                                                <span class="font-semibold tabular-nums {{ $text[$r['tone']] }}">{{ $r['score'] }}%</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <details class="rounded-lg border border-line">
                                    <summary class="cursor-pointer px-3 py-2 text-sm text-sky focus-visible:outline-2 focus-visible:outline-blue">More detail</summary>
                                    <div class="grid grid-cols-2 gap-2 p-3">
                                        <x-stat-tile :compact="true" label="Quizzes done" :value="$s['quiz_stats']['completed']" tone="peach" />
                                        <x-stat-tile :compact="true" label="Quiz average" :value="$s['quiz_stats']['average'] . '%'" />
                                        <x-stat-tile :compact="true" label="Past paper average" :value="$s['past_paper_average'] . '%'" tone="sky" />
                                        <x-stat-tile :compact="true" label="Question accuracy" :value="$s['questions']['accuracy'] . '%'" tone="good" />
                                    </div>
                                </details>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>

        {{-- 5. Recent assessment activity (completion dates only; no study-time claims) --}}
        <section aria-labelledby="activity-heading" class="mt-6 {{ $card }}">
            <h2 id="activity-heading" class="text-lg font-normal">Recent assessments</h2>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-stat-tile label="Last 30 days" :value="$a['completed_30d']"
                    :sub="$a['delta'] === 0 ? 'Same as the 30 days before' : (($a['delta'] > 0 ? '+' : '−') . abs($a['delta']) . ' vs the 30 days before')" tone="peach" />
                <x-stat-tile label="Active weeks" :value="$a['weeks_active'] . ' of ' . $a['weeks_total']" sub="weeks with at least one assessment" tone="sky" />
                <x-stat-tile label="Most recent" :value="$a['last_ago'] ?? 'None yet'" :sub="$a['last_label']" />
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[30rem] text-left text-sm">
                    <caption class="sr-only">The six most recent quizzes and past papers</caption>
                    <thead>
                        <tr class="border-b border-line text-[11px] uppercase tracking-wider text-muted">
                            <th scope="col" class="py-3 pr-4 font-semibold">Assessment</th>
                            <th scope="col" class="py-3 pr-4 font-semibold">Subject</th>
                            <th scope="col" class="py-3 pr-4 font-semibold">Score</th>
                            <th scope="col" class="py-3 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['recent'] as $r)
                            <tr class="border-b border-line/70">
                                <td class="py-3 pr-4 text-white">{{ $r['title'] }} <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $r['type'] === 'quiz' ? 'bg-blue/15 text-sky' : 'bg-brand/15 text-brand' }}">{{ $r['type_label'] }}</span></td>
                                <td class="py-3 pr-4 text-slate-200">{{ $r['subject'] }}</td>
                                <td class="py-3 pr-4 font-semibold tabular-nums {{ $text[$r['tone']] }}">{{ $r['score'] }}%</td>
                                <td class="py-3 text-slate-200">{{ $r['date_label'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-muted">Based on quizzes and past papers completed on GradeGenius.</p>
        </section>

        <details class="mt-6 rounded-2xl border border-line bg-card p-5 text-sm text-muted">
            <summary class="cursor-pointer text-slate-200">How these numbers are calculated</summary>
            <ul class="mt-3 list-disc space-y-1 pl-5">
                <li><strong class="text-slate-200">Subject mark:</strong> the learner's average for that subject in the latest term.</li>
                <li><strong class="text-slate-200">Current average:</strong> the mean of all subject marks, the same number the learner sees.</li>
                <li><strong class="text-slate-200">Level:</strong> the 1 to 7 achievement scale used on school reports (7 is 80% or more).</li>
                <li><strong class="text-slate-200">Goal:</strong> the target set for each subject. "Needs support" means below that goal.</li>
                <li><strong class="text-slate-200">Topic percentages:</strong> how well the learner is doing on questions from that topic.</li>
            </ul>
        </details>
    </div>
@endsection
