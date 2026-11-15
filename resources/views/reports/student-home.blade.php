@extends('layouts.student', ['title' => 'Dashboard', 'active' => 'home'])

@php
    $focus = $dashboard['focus'];
    $snapshot = $dashboard['snapshot'];
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad', 'sky' => 'text-sky'];
    $bg = ['good' => 'bg-good', 'mid' => 'bg-warn', 'low' => 'bg-bad', 'sky' => 'bg-sky'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
    $soon = 'shrink-0 whitespace-nowrap rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted';
    $changeText = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')).abs($n).' pts';
    $reportUrl = route('student.report');
@endphp

@section('student-content')
    @include('partials.print-header', ['title' => 'Learner Academic Performance Overview', 'student' => $dashboard['student']])

    <div class="space-y-5">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl font-normal tracking-tight text-ink sm:text-4xl">Welcome back, {{ \Illuminate\Support\Str::before($dashboard['student']['name'], ' ') }}</h1>
                <p class="mt-2 text-muted">Grade {{ $dashboard['student']['grade'] }} · Your learning overview</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-ink">
                <span class="text-muted">Overall goal</span>
                <span class="font-semibold {{ $text[$dashboard['status']['tone']] }}">{{ $dashboard['status']['label'] }}</span>
            </span>
        </header>

        <div class="grid gap-5 lg:grid-cols-12">
            {{-- 1. Focus: the subject furthest from its own goal --}}
            <section aria-labelledby="focus-heading" class="relative overflow-hidden {{ $card }} lg:col-span-5">
                <div class="pointer-events-none absolute -right-12 -top-12 size-44 rounded-full bg-brand/25 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <h2 id="focus-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'target', 'size' => 20])
                        Your focus
                    </h2>
                    @if ($focus && $focus['status']['state'] === 'met')
                        <p class="mt-4 text-2xl font-semibold text-good">Every subject is at goal</p>
                        <p class="mt-2 text-sm text-muted">Nothing is below its target right now. Keep practising to stay on track.</p>
                        <a href="{{ $reportUrl }}" class="mt-5 {{ $link }}">Open Academic Performance @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
                    @elseif ($focus)
                        <p class="mt-4 text-sm text-muted">Furthest from its goal</p>
                        <p class="mt-1 text-3xl font-semibold text-ink">{{ $focus['name'] }}</p>
                        <p class="mt-2 text-sm text-muted">
                            <span class="font-semibold tabular-nums text-ink">{{ $focus['current'] }}%</span> now · goal {{ $focus['target'] }}% ·
                            <span class="font-medium {{ $text[$focus['status']['tone']] }}">{{ $focus['status']['detail'] }}</span>
                        </p>
                        <div class="relative mt-4 h-2 rounded-full bg-line" role="img" aria-label="{{ $focus['name'] }}: {{ $focus['current'] }} percent, goal {{ $focus['target'] }} percent">
                            <div class="h-full rounded-full {{ $bg[$focus['status']['tone']] }}" style="width: {{ $focus['current'] }}%"></div>
                            <span class="absolute -top-1 h-4 w-0.5 rounded bg-white shadow-xs" style="left: {{ $focus['target'] }}%"></span>
                        </div>
                        @if ($focus['weakest_topic'])
                            <p class="mt-4 text-sm text-muted">Start with <strong class="font-semibold text-ink">{{ $focus['weakest_topic']['name'] }}</strong>, your lowest topic here at {{ $focus['weakest_topic']['mastery'] }}% mastery.</p>
                        @endif
                        <div class="mt-5 flex flex-wrap items-center gap-2.5">
                            <a href="{{ $reportUrl }}#subject-{{ $focus['key'] }}" class="{{ $link }}">
                                <span>View {{ $focus['name'] }} detail</span>
                                @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])
                            </a>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-muted">No subjects to show yet.</p>
                    @endif
                </div>
            </section>

            {{-- 2. Academic snapshot --}}
            <section aria-label="Academic snapshot" class="grid auto-rows-fr grid-cols-2 gap-3 lg:col-span-7">
                <x-stat-tile label="Overall average" :value="$snapshot['average'].'%'" :sub="$changeText($snapshot['change']).' since '.$snapshot['first_term']" />
                <x-stat-tile label="Subjects at goal" :value="$snapshot['at_target'].' / '.$snapshot['subject_count']" :sub="$snapshot['below_goal'] > 0 ? $snapshot['below_goal'].' below goal' : 'All at goal'" tone="peach" />
                <x-stat-tile label="Questions answered" :value="number_format($snapshot['questions_answered'])" sub="Across all subjects" />
                <x-stat-tile label="Question accuracy" :value="$snapshot['accuracy'] === null ? '—' : $snapshot['accuracy'].'%'" sub="Correct answers overall" tone="good" />
            </section>

            {{-- 3. Next learning actions: lowest topic mastery across every subject --}}
            <section aria-labelledby="actions-heading" class="{{ $card }} lg:col-span-7">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="actions-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                        Your next learning actions
                    </h2>
                </div>
                <p class="mt-1 text-sm text-muted">Chosen from your lowest topic mastery across all subjects.</p>

                @if (count($dashboard['actions']) > 0)
                    <div class="mt-4 space-y-3">
                        @foreach ($dashboard['actions'] as $action)
                            <x-action-card :title="'Revise '.$action['topic']" :subject="$action['subject']" :mastery="$action['mastery']" :priority="$action['priority']" :tone="$action['tone']" :color="$action['color']" :href="$reportUrl.'#subject-'.$action['subject_key']" actionText="View detail" />
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">No weak topics right now. Keep practising to stay on track.</p>
                @endif
            </section>

            {{-- 4. Subject overview --}}
            <section aria-labelledby="subjects-heading" class="{{ $card }} lg:col-span-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 id="subjects-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'bar-chart', 'size' => 20])
                        Subject overview
                    </h2>
                    <a href="{{ $reportUrl }}#subjects-heading" class="text-xs font-medium text-ink underline-offset-4 hover:underline">View details</a>
                </div>
                <p class="mt-1 text-sm text-muted">Bars turn amber or red when a subject is below its goal.</p>
                <ul class="mt-4 space-y-1">
                    @foreach ($dashboard['subjects'] as $subject)
                        <li>
                            <a href="{{ $reportUrl }}#subject-{{ $subject['key'] }}" class="block rounded-lg px-2 py-1.5 transition hover:bg-card-2 focus-visible:outline-2 focus-visible:outline-blue">
                                <x-bar-row :label="$subject['name']" :value="$subject['current']" :tone="$subject['tone']" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- 5. Recent activity: latest quizzes and past papers --}}
            <section aria-labelledby="activity-heading" class="{{ $card }} lg:col-span-7">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="activity-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'zap', 'size' => 20])
                        Recent activity
                    </h2>
                    <span class="text-xs text-muted">Latest assessments</span>
                </div>
                @if (count($dashboard['activity']) > 0)
                    <ul class="mt-3 divide-y divide-line/70">
                        @foreach ($dashboard['activity'] as $row)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-ink text-sm">{{ $row['title'] }}</p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $row['subject'] }} · {{ $row['type_label'] }} · {{ $row['date_label'] }}</p>
                                </div>
                                <span class="text-base font-semibold tabular-nums {{ $text[$row['tone']] }}">{{ $row['score'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-muted">Complete a quiz or past paper and it will show up here.</p>
                @endif
            </section>

            {{-- 6. Not built yet: one compact card instead of two mostly empty ones --}}
            <section aria-label="Coming soon" class="{{ $card }} lg:col-span-5">
                <ul class="space-y-5">
                    <li>
                        <div class="flex items-center justify-between gap-2">
                            <h2 id="planner-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                                @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                                Study planner
                            </h2>
                            <span class="{{ $soon }}">Coming soon</span>
                        </div>
                        <p class="mt-2 text-sm text-muted">Plan revision sessions and tick them off here. Nothing is scheduled yet because the planner isn't set up.</p>
                    </li>
                    <li class="border-t border-line pt-5">
                        <div class="flex items-center justify-between gap-2">
                            <h2 id="exams-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                                @include('partials.icon', ['name' => 'calendar', 'size' => 20])
                                Upcoming exams
                            </h2>
                            <span class="{{ $soon }}">Coming soon</span>
                        </div>
                        <p class="mt-2 text-sm text-muted">No exams scheduled yet. Dates and a countdown will appear here once an exam schedule is added.</p>
                    </li>
                </ul>
            </section>
        </div>
    </div>
@endsection
