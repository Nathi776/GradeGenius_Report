@extends('layouts.student', ['title' => 'Dashboard', 'active' => 'home'])

@php
    $focus = $dashboard['focus'];
    $snapshot = $dashboard['snapshot'];
    $assessments = $dashboard['assessment_snapshot'];
    $student = $dashboard['student'];
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad'];
    $bg = ['good' => 'bg-good', 'mid' => 'bg-warn', 'low' => 'bg-bad'];
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
    $soon = 'shrink-0 whitespace-nowrap rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted';
    $termLabel = $selectedTerm === 'all' ? 'All Terms' : ($terms[(int) $selectedTerm] ?? 'Selected Term');
    $reportUrl = $selectedTerm === 'all' ? route('student.report') : route('student.report', ['term' => $selectedTerm]);
    $quizAverageSub = $assessments['quiz_average'] === null ? 'No quiz scores yet' : 'Average: '.$assessments['quiz_average'].'%';
    $pastPaperAverageSub = $assessments['past_paper_average'] === null ? 'None taken yet' : 'Average: '.$assessments['past_paper_average'].'%';
@endphp

@section('student-content')
    @include('partials.print-header', ['title' => 'Learner Academic Performance Overview', 'student' => $student, 'term' => $termLabel])

    <div class="gg-portal-dashboard space-y-5">
        <header class="{{ $card }} flex flex-col gap-5 bg-card p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-4 sm:items-center">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-sky font-semibold text-2xl text-black">{{ strtoupper(substr(trim($student['name']), 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Student portal · Learner view</p>
                    <h1 class="mt-1 break-words text-3xl font-semibold tracking-tight text-ink sm:text-4xl">{{ $student['name'] }}</h1>
                    <p class="mt-1 text-sm text-muted">Grade {{ $student['grade'] }} · {{ $termLabel }}</p>
                    <p class="mt-2 text-sm font-medium text-ink">Current average {{ $snapshot['average'] }}%@if ($snapshot['questions_answered'] > 0 && $snapshot['accuracy'] !== null) · {{ number_format($snapshot['questions_answered']) }} questions · {{ $snapshot['accuracy'] }}% accuracy @endif</p>
                    <p class="mt-1 text-xs text-muted">Activity sharing: <span class="font-semibold text-ink">{{ auth()->user()->shares_activity ? 'On' : 'Off' }}</span> · <a href="{{ route('student.settings') }}" class="underline underline-offset-2 hover:text-blue focus-visible:outline-2 focus-visible:outline-blue">Manage in Settings</a></p>
                </div>
            </div>
            <div class="grid shrink-0 gap-3 lg:max-w-[22rem]">
                <section aria-label="Study streak" class="gg-brutal-card flex min-w-48 items-center gap-3 rounded-2xl border-2 border-black bg-butter p-3.5">
                    <span aria-hidden="true" class="text-2xl">🔥</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink">Study streak</p>
                        <p class="mt-1 font-semibold text-ink">{{ $dashboard['study_stats']['streak'] ?? 0 }} {{ ($dashboard['study_stats']['streak'] ?? 0) === 1 ? 'day' : 'days' }}</p>
                        <p class="text-[11px] text-ink">{{ ($dashboard['study_stats']['streak'] ?? 0) > 0 ? 'Consecutive days studied' : 'Study sessions completed' }}</p>
                    </div>
                </section>
                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    <span class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-ink">
                        <span class="text-muted">Overall goal</span>
                        <span class="font-semibold {{ $text[$dashboard['status']['tone']] }}">{{ $dashboard['status']['label'] }}</span>
                    </span>
                    <a href="{{ $reportUrl }}" class="{{ $link }}">Full academic report @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])</a>
                </div>
            </div>
        </header>

        <section aria-label="Student progress summary" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat-tile label="Weekly study goal" :value="($dashboard['study_stats']['weekly_minutes'] ?? 0).' / '.($dashboard['study_stats']['weekly_goal'] ?? 630).' min'" :sub="round((($dashboard['study_stats']['weekly_minutes'] ?? 0) / ($dashboard['study_stats']['weekly_goal'] ?? 630)) * 100).'% of weekly target completed'" tone="mid" />
            <x-stat-tile label="Quizzes taken" :value="$assessments['quizzes_completed']" :sub="$quizAverageSub" tone="peach" />
            <x-stat-tile label="Practice exams taken" :value="$assessments['past_papers_completed']" :sub="$pastPaperAverageSub" tone="sky" />
            <x-stat-tile label="Study minutes" :value="($dashboard['study_stats']['real_study_minutes'] ?? 0).' min'" sub="Verified counted sessions" tone="good" />
        </section>

        <div class="grid gap-5 lg:grid-cols-12">
            <section aria-labelledby="actions-heading" class="{{ $card }} lg:col-span-7">
                <h2 id="actions-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                    Your next learning actions
                </h2>
                <p class="mt-1 text-sm text-muted">Suggestions based on your lowest recorded topic mastery.</p>
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

            <section aria-labelledby="activity-heading" class="{{ $card }} lg:col-span-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="activity-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'zap', 'size' => 20])
                        Recent assessment activity
                    </h2>
                    <span class="text-xs text-muted">Recorded results</span>
                </div>
                <p class="mt-1 text-sm text-muted">Latest quizzes and past papers across all subjects.</p>
                @if (count($dashboard['activity']) > 0)
                    <ul class="mt-3 divide-y divide-line/70">
                        @foreach ($dashboard['activity'] as $row)
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
                    <p class="mt-4 text-sm text-muted">Complete a quiz or past paper and it will appear here.</p>
                @endif
                <a href="{{ $reportUrl }}#assess-heading" class="{{ $link }} mt-3">Open assessment table @include('partials.icon', ['name' => 'arrow-right', 'size' => 14])</a>
            </section>
        </div>

        <section aria-labelledby="subjects-heading" class="{{ $card }}">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="subjects-heading" class="text-xl font-semibold text-ink">My enrolled subjects</h2>
                    <p class="mt-1 text-sm text-muted">Current subject marks, goals, and recorded assessment results.</p>
                </div>
                <a href="{{ $reportUrl }}#subjects-heading" class="{{ $link }}">View all subject details @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])</a>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($dashboard['subjects'] as $subject)
                    @php
                        $subjectUrl = $reportUrl.'#subject-'.$subject['key'];
                        $isFocus = $focus && $subject['key'] === $focus['key'] && $focus['status']['state'] !== 'met';
                    @endphp
                    <article class="gg-brutal-card flex h-full flex-col rounded-2xl border-2 border-black p-4 sm:p-5 {{ $isFocus ? 'bg-butter' : 'bg-card' }}">
                        <div class="flex items-start justify-between gap-2">
                            <span class="min-w-0 font-semibold text-ink">{{ $subject['name'] }}</span>
                            <span class="shrink-0 rounded-full border border-line bg-card-2 px-2.5 py-0.5 text-xs font-semibold {{ $text[$subject['tone']] }}">{{ $subject['status_label'] }}</span>
                        </div>

                        <div class="mt-4 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-3xl font-semibold tabular-nums text-ink">{{ $subject['current'] }}%</p>
                                <p class="text-xs text-muted">Current average</p>
                            </div>
                            <p class="text-right text-xs font-medium text-muted">Goal {{ $subject['target'] }}%</p>
                        </div>
                        <div class="relative mt-3 h-2 rounded-full bg-line/50" role="img" aria-label="{{ $subject['name'] }}: {{ $subject['current'] }} percent, goal {{ $subject['target'] }} percent">
                            <div class="h-full rounded-full {{ $bg[$subject['tone']] }}" style="width: {{ min(100, max(0, $subject['current'])) }}%"></div>
                            <span class="absolute -top-1 h-4 w-0.5 rounded bg-ink/70" style="left: {{ min(100, max(0, $subject['target'])) }}%"></span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="rounded-xl border border-line bg-card-2 p-3">
                                <p class="text-xs text-muted">Quizzes</p>
                                <p class="mt-1 font-semibold text-ink">{{ $subject['quiz_count'] }}</p>
                                <p class="text-xs text-muted">{{ $subject['quiz_average'] === null ? 'No scores' : 'Avg. '.$subject['quiz_average'].'%' }}</p>
                            </div>
                            <div class="rounded-xl border border-line bg-card-2 p-3">
                                <p class="text-xs text-muted">Past papers</p>
                                <p class="mt-1 font-semibold text-ink">{{ $subject['past_paper_count'] }}</p>
                                <p class="text-xs text-muted">{{ $subject['past_paper_average'] === null ? 'No scores' : 'Avg. '.$subject['past_paper_average'].'%' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex-1 border-t border-line pt-3">
                            @if ($subject['weakest_topic'])
                                <p class="text-xs text-muted">Topic to practise</p>
                                <p class="mt-1 text-sm font-medium text-ink">{{ $subject['weakest_topic']['name'] }} <span class="font-normal text-muted">· {{ $subject['weakest_topic']['mastery'] }}% mastery</span></p>
                            @else
                                <p class="text-sm text-muted">No topic currently needs extra attention.</p>
                            @endif
                            @if ($isFocus)
                                <span class="mt-2 inline-block rounded-full border border-black bg-white px-2.5 py-0.5 text-xs font-semibold text-black">Suggested focus</span>
                            @endif
                        </div>

                        <a href="{{ $subjectUrl }}" class="{{ $link }} mt-4 w-fit">View subject report @include('partials.icon', ['name' => 'arrow-right', 'size' => 14])</a>
                    </article>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="trend-heading" class="{{ $card }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="trend-heading" class="text-lg font-normal text-ink">Academic trend across all terms</h2>
                    <p class="mt-1 text-sm text-muted">Plotted from the marks recorded for each term. Terms without a recorded mark are left empty.</p>
                </div>
                <a href="{{ $reportUrl }}" class="{{ $link }}">Full report @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])</a>
            </div>
            <div class="mt-4">
                <x-line-chart :series="$dashboard['trend']['series']" :labels="$dashboard['trend']['labels']" id="student-home-term-trend" :height="190" />
            </div>
        </section>

        <section aria-label="Planned learning features" class="{{ $card }} grid gap-6 md:grid-cols-2">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                        Study planner
                    </h2>
                </div>
                <p class="mt-3 text-sm text-muted">Turn your weakest topics into short revision sessions across the week.</p>
                <a href="{{ route('student.planner') }}" class="mt-3 inline-flex items-center rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-blue focus-visible:outline-2 focus-visible:outline-blue">Open the planner</a>
            </div>
            <div class="border-t border-line pt-5 md:border-l md:border-t-0 md:pl-6 md:pt-0">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 20])
                        Upcoming exams
                    </h2>
                    @if (count($dashboard['exams']) === 0)
                        <span class="{{ $soon }}">Coming soon</span>
                    @endif
                </div>
                @if (count($dashboard['exams']) > 0)
                    <ul class="mt-3 divide-y divide-line/70">
                        @foreach ($dashboard['exams'] as $exam)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-ink">{{ $exam['subject'] }}</p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $exam['title'] }}@if ($exam['paper']) · {{ $exam['paper'] }}@endif · {{ $exam['date_label'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm font-semibold {{ $exam['days_until'] <= 14 ? 'text-warn' : 'text-muted' }}">{{ $exam['countdown'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-sm text-muted">No exam schedule is available yet. Dates will appear here once a schedule is added.</p>
                @endif
            </div>
        </section>
    </div>
@endsection
