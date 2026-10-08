@extends('layouts.app', ['title' => 'Your Academic Report'])

@php
    $overall = $report['overall'];
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad'];
    $bg = ['good' => 'bg-good', 'mid' => 'bg-warn', 'low' => 'bg-bad'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $changeText = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')) . abs($n) . ' pts';
    $changeClass = fn ($n) => $n > 0 ? 'text-good' : ($n < 0 ? 'text-bad' : 'text-muted');
@endphp

@section('content')
    <div class="mx-auto w-full max-w-4xl">
        <header>
        <h1 class="text-3xl font-normal tracking-tight sm:text-4xl">Your Academic Report</h1>
        <p class="mt-2 text-muted">Track your marks, assessments, and learning progress across all {{ $overall['subject_count'] }} subjects.</p>
        </header>

    {{-- 1. Headline numbers (all derived from the subject list below) --}}
    <section aria-label="Summary" class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat-tile label="Current average" :value="$overall['current'] . '%'" :sub="$overall['current_term']" />
        <x-stat-tile label="Improvement" :value="$changeText($overall['change'])" :sub="'Since ' . $overall['first_term']" :tone="$overall['change'] > 0 ? 'good' : ($overall['change'] < 0 ? 'low' : 'default')" />
        <x-stat-tile label="Subjects" :value="$overall['subject_count']" :sub="$overall['at_target'] . ' of ' . $overall['subject_count'] . ' at goal'" tone="peach" />
        <x-stat-tile label="Goal" :value="$overall['target_status']['label']" :sub="$overall['target'] . '% goal · ' . $overall['target_status']['detail']" :tone="$overall['target_status']['tone'] === 'good' ? 'sky' : $overall['target_status']['tone']" />
    </section>

    {{-- 2. Progress over time: overall + every subject --}}
    <section aria-labelledby="progress-heading" class="mt-6 {{ $card }}" data-chart>
        <h2 id="progress-heading" class="flex items-center gap-2 text-lg font-normal">
            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/></svg>
            Progress over time
        </h2>
        <div class="mt-4">
            <x-line-chart :series="$report['chart']['series']" :labels="$report['terms']" id="progress" />
        </div>
        <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Choose which lines to show">
            @foreach ($report['chart']['series'] as $s)
                <button type="button" data-series-toggle="{{ $s['key'] }}" aria-pressed="true"
                    class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-muted opacity-50 transition aria-pressed:text-white aria-pressed:opacity-100 focus-visible:outline-2 focus-visible:outline-blue">
                    <span class="size-2.5 rounded-full" style="background: {{ $s['color'] }}"></span>
                    {{ $s['name'] }}
                </button>
            @endforeach
        </div>
    </section>

    {{-- 3. Subjects: one collapsible card per subject, loops over whatever the learner takes --}}
    <section aria-labelledby="subjects-heading" class="mt-6 {{ $card }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="subjects-heading" class="text-lg font-normal">Subjects</h2>
                <p class="mt-1 text-sm text-muted">Open a subject for its topics, past papers and quizzes. The one furthest from its goal starts open.</p>
            </div>
            <button type="button" data-toggle-all class="rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm text-sky hover:border-blue focus-visible:outline-2 focus-visible:outline-blue">Expand all</button>
        </div>

        <div class="mt-4 space-y-3">
            @foreach ($report['subjects'] as $s)
                @php $status = $s['target_status']; @endphp
                <details data-subject-card id="subject-{{ $s['key'] }}" class="group rounded-xl border border-line bg-card-2 open:border-blue/40" @if ($s['key'] === $report['focus_key']) open @endif>
                    <summary class="flex cursor-pointer list-none flex-wrap items-center gap-x-5 gap-y-3 rounded-xl p-4 focus-visible:outline-2 focus-visible:outline-blue [&::-webkit-details-marker]:hidden">
                        <span class="flex min-w-44 flex-1 items-center gap-3">
                            <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $s['color'] }}"></span>
                            <span class="font-medium text-white">{{ $s['name'] }}</span>
                        </span>
                        <span class="flex items-baseline gap-2">
                            <span class="text-2xl font-semibold tabular-nums text-white">{{ $s['current'] }}%</span>
                            <span class="text-sm font-medium {{ $changeClass($s['change']) }}">{{ $changeText($s['change']) }}<span class="sr-only"> since {{ $overall['first_term'] }}</span></span>
                        </span>
                        <x-sparkline :points="$s['series']" :color="$s['color']" class="hidden sm:block" />
                        <span class="w-full sm:w-44">
                            <span class="relative block h-1.5 rounded-full bg-line">
                                <span class="absolute inset-y-0 left-0 rounded-full {{ $bg[$status['tone']] }}" style="width: {{ $s['current'] }}%"></span>
                                <span class="absolute -top-1 h-3.5 w-0.5 rounded bg-white" style="left: {{ $s['target'] }}%"></span>
                            </span>
                            <span class="mt-1.5 block text-xs {{ $text[$status['tone']] }}">Goal {{ $s['target'] }}% · {{ $status['detail'] }}</span>
                        </span>
                        <svg class="ml-auto size-4 shrink-0 text-muted transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>

                    <div class="grid gap-8 border-t border-line p-4 sm:p-5 lg:grid-cols-2">
                        {{-- left: topics and past papers --}}
                        <div class="space-y-7">
                            <div>
                                <h3 class="text-sm font-semibold text-white">Topic mastery</h3>
                                <div class="mt-3 space-y-3">
                                    @foreach ($s['topics'] as $t)
                                        <x-bar-row :label="$t['name']" :value="$t['mastery']" :tone="$t['tone']" />
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">Past papers</h3>
                                <div class="mt-3 space-y-3">
                                    @foreach ($s['past_papers'] as $p)
                                        <x-bar-row :label="$p['title']" :value="$p['score']" variant="paper" />
                                    @endforeach
                                </div>
                                <div class="mt-4 flex items-center justify-between border-t border-line pt-3 text-sm">
                                    <span class="font-semibold text-white">Average</span>
                                    <span class="font-semibold text-blue">{{ $s['past_paper_average'] }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- right: quizzes, questions, strengths --}}
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-sm font-semibold text-white">Quiz analytics</h3>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <x-stat-tile :compact="true" label="Completed" :value="$s['quiz_stats']['completed']" tone="peach" />
                                    <x-stat-tile :compact="true" label="Average" :value="$s['quiz_stats']['average'] . '%'" />
                                    <x-stat-tile :compact="true" label="Accuracy" :value="$s['questions']['accuracy'] . '%'" tone="sky" />
                                    <x-stat-tile :compact="true" label="Highest" :value="$s['quiz_stats']['highest'] . '%'" tone="good" />
                                    <x-stat-tile :compact="true" label="Lowest" :value="$s['quiz_stats']['lowest'] . '%'" tone="low" />
                                    <x-stat-tile :compact="true" label="Avg attempts" :value="$s['quiz_stats']['avg_attempts']" tone="sky" />
                                </div>

                                @php $chrono = array_reverse($s['quizzes']); usort($chrono, fn ($a, $b) => strcmp($a['date'], $b['date'])); @endphp
                                <p class="mt-4 text-xs text-muted">Quiz scores, oldest to newest</p>
                                <div class="mt-2 flex h-16 items-end gap-1" role="img" aria-label="Quiz scores from oldest to newest: {{ implode(', ', array_column($chrono, 'score')) }} percent">
                                    @foreach ($chrono as $q)
                                        @php $qt = \App\Services\StudentReportService::tone($q['score']); @endphp
                                        <div class="min-w-1 flex-1 rounded-t {{ $bg[$qt] }}" style="height: {{ $q['score'] }}%" title="{{ $q['title'] }}: {{ $q['score'] }}%"></div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <p class="text-sm text-white">Questions attempted: <strong>{{ $s['questions']['attempted'] }}</strong></p>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-good/10 p-3 text-center">
                                        <p class="text-2xl font-semibold text-good">{{ $s['questions']['correct'] }}</p>
                                        <p class="text-xs text-muted">Correct ({{ $s['questions']['accuracy'] }}%)</p>
                                    </div>
                                    <div class="rounded-xl bg-bad/10 p-3 text-center">
                                        <p class="text-2xl font-semibold text-bad">{{ $s['questions']['incorrect'] }}</p>
                                        <p class="text-xs text-muted">Incorrect ({{ 100 - $s['questions']['accuracy'] }}%)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <h3 class="text-sm font-medium text-good">Strongest</h3>
                                    <ul class="mt-2 space-y-1 text-sm text-good">
                                        @forelse ($s['strongest'] as $t)
                                            <li><span aria-hidden="true">✓</span> {{ $t['name'] }} <span class="text-muted">{{ $t['mastery'] }}%</span></li>
                                        @empty
                                            <li class="text-muted">No topic at 70% or more yet</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-brand">Needs attention</h3>
                                    <ul class="mt-2 space-y-1 text-sm text-brand">
                                        @forelse ($s['attention'] as $t)
                                            <li><span aria-hidden="true">⚠</span> {{ $t['name'] }} <span class="text-muted">{{ $t['mastery'] }}%</span></li>
                                        @empty
                                            <li class="text-muted">Nothing below 65%</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    {{-- 4. Assessments across all subjects, filterable --}}
    @php $stats = $report['assessment_stats']; @endphp
    <section aria-labelledby="assess-heading" class="mt-6 {{ $card }}">
        <h2 id="assess-heading" class="flex items-center gap-2 text-lg font-normal">
            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 13h4M10 17h4"/></svg>
            Assessment performance
        </h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <x-stat-tile label="Quizzes completed" :value="$stats['quizzes_completed']" tone="peach" />
            <x-stat-tile label="Past papers" :value="$stats['past_papers']" tone="sky" />
            <x-stat-tile label="Average score" :value="$stats['average_score'] . '%'" />
            <x-stat-tile label="Question accuracy" :value="$stats['quiz_accuracy'] . '%'" tone="good" />
            <x-stat-tile label="Questions answered" :value="$stats['questions_answered']" tone="peach" />
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-base text-white">Recent assessments</h3>
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by subject">
                <button type="button" data-filter="all" aria-pressed="true" class="rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-muted aria-pressed:border-white/60 aria-pressed:text-white focus-visible:outline-2 focus-visible:outline-blue">All subjects</button>
                @foreach ($report['subjects'] as $s)
                    <button type="button" data-filter="{{ $s['key'] }}" aria-pressed="false" class="rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-muted aria-pressed:border-white/60 aria-pressed:text-white focus-visible:outline-2 focus-visible:outline-blue">{{ $s['name'] }}</button>
                @endforeach
            </div>
        </div>

        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[34rem] text-left text-sm" data-assessments>
                <caption class="sr-only">Quizzes and past papers across all subjects, newest first</caption>
                <thead>
                    <tr class="border-b border-line text-[11px] uppercase tracking-wider text-muted">
                        <th scope="col" class="py-3 pr-4 font-semibold">Assessment</th>
                        <th scope="col" class="py-3 pr-4 font-semibold">Type</th>
                        <th scope="col" class="py-3 pr-4 font-semibold">Subject</th>
                        <th scope="col" class="py-3 pr-4 font-semibold">Score</th>
                        <th scope="col" class="py-3 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['assessments'] as $a)
                        <tr data-subject="{{ str()->slug($a['subject']) }}" class="border-b border-line/70">
                            <td class="py-3 pr-4 text-white">{{ $a['title'] }}</td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs {{ $a['type'] === 'quiz' ? 'bg-blue/15 text-sky' : 'bg-brand/15 text-brand' }}">{{ $a['type_label'] }}</span>
                            </td>
                            <td class="py-3 pr-4 text-slate-200">{{ $a['subject'] }}</td>
                            <td class="py-3 pr-4 font-semibold tabular-nums {{ $text[$a['tone']] }}">{{ $a['score'] }}%</td>
                            <td class="py-3 text-slate-200">{{ $a['date_label'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p data-empty hidden class="py-6 text-center text-sm text-muted">No assessments for this subject yet.</p>
        </div>
        <button type="button" data-show-more class="mt-3 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm text-sky hover:border-blue focus-visible:outline-2 focus-visible:outline-blue">Show all {{ count($report['assessments']) }}</button>
    </section>

        <details class="mt-6 rounded-2xl border border-line bg-card p-5 text-sm text-muted">
        <summary class="cursor-pointer text-slate-200">How these numbers are calculated</summary>
        <ul class="mt-3 list-disc space-y-1 pl-5">
            <li><strong class="text-slate-200">Subject average:</strong> the learner's average for that subject in the latest term.</li>
            <li><strong class="text-slate-200">Current average:</strong> the mean of all subject averages. The chart's last point is this same number.</li>
            <li><strong class="text-slate-200">Improvement:</strong> change in percentage points between {{ $overall['first_term'] }} and {{ $overall['current_term'] }}.</li>
            <li><strong class="text-slate-200">Average score:</strong> mean of every quiz and past paper score.</li>
            <li><strong class="text-slate-200">Question accuracy:</strong> correct answers divided by questions answered, across all subjects.</li>
        </ul>
        </details>
    </div>
@endsection
