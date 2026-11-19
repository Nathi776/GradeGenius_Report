@extends('layouts.parent', ['title' => 'Parent dashboard', 'active' => 'home'])

@php
    $child = $dashboard['child'];
    $snap = $dashboard['snapshot'];
    $act = $dashboard['activity'];
    $guardian = auth()->user();
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad', 'sky' => 'text-sky'];
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
    $soon = 'shrink-0 whitespace-nowrap rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted';
    $pts = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')).abs($n).' pts';
    $linkedLearnerCount = count($linkedLearners);
    $linkedLearnerLabel = $linkedLearnerCount === 1 ? 'linked learner account' : 'linked learner accounts';
    $reportUrl = route('parent.report', ['child' => $dashboard['active_child']])
        .($selectedTerm === 'all' ? '' : '?term='.$selectedTerm);
    $refreshUrl = route('parent.home', [
        'child' => $dashboard['active_child'],
        'term' => $selectedTerm,
        'learner' => $selectedLearner['id'] ?? null,
    ]);
@endphp

@section('parent-content')
    @include('partials.print-header', ['title' => 'Parent & Guardian Academic Progress Report', 'student' => $dashboard['child'], 'term' => $dashboard['snapshot']['term']])

    <div class="gg-portal-dashboard space-y-5">
        <header class="{{ $card }} flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-lavender font-semibold text-2xl text-black">{{ strtoupper(substr(trim($guardian->name), 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Parent portal · Guardian view</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-semibold tracking-tight text-ink sm:text-4xl">{{ $guardian->name }}</h1>
                        @if ($guardian->hasVerifiedEmail())
                            <span class="rounded-full border border-line bg-good/20 px-2.5 py-0.5 text-xs font-semibold text-ink">Verified guardian account</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-muted">Monitoring {{ $linkedLearnerCount }} {{ $linkedLearnerLabel }} on Grade Genius</p>
                </div>
            </div>
            <a href="{{ $refreshUrl }}" class="gg-brutal-card inline-flex items-center gap-2 rounded-xl border-2 border-black bg-butter px-4 py-3 text-sm font-semibold text-ink transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-blue">
                <span aria-hidden="true" class="text-lg">↻</span>
                Refresh learner activity
            </a>
        </header>

        <section aria-labelledby="registered-learners-heading" class="{{ $card }}">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="registered-learners-heading" class="text-xl font-semibold text-ink">Select registered learner</h2>
                    <p class="mt-1 text-sm text-muted">Review sign-in status and activity for student accounts linked to your verified parent account.</p>
                </div>
                <span class="{{ $soon }}">Learner activity</span>
            </div>
            @if ($linkedLearnerCount > 0)
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($linkedLearners as $learner)
                        @php($isOnline = $learner['status_label'] === 'Online')
                        @php($isSelected = $learner['id'] === ($selectedLearner['id'] ?? null))
                        <article class="gg-brutal-card rounded-2xl border-2 border-black p-4 {{ $isSelected ? 'bg-butter' : 'bg-card' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex size-12 shrink-0 items-center justify-center rounded-xl border-2 border-black {{ $loop->odd ? 'bg-sky' : 'bg-good/30' }} font-semibold text-lg text-ink">{{ strtoupper(substr(trim($learner['name']), 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <h3 class="truncate font-semibold text-ink">{{ $learner['name'] }}</h3>
                                        <p class="truncate text-xs text-muted">{{ $learner['email'] }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border-2 border-black {{ $isOnline ? 'bg-good/30' : 'bg-card-2' }} px-2.5 py-1 text-xs font-semibold text-ink">
                                    <span aria-hidden="true" class="size-2 rounded-full {{ $isOnline ? 'bg-good' : 'bg-muted' }}"></span>
                                    {{ $learner['status_label'] }}
                                </span>
                            </div>
                            <div class="mt-4 rounded-xl border-2 border-black bg-card-2 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Learner details</p>
                                <p class="mt-1 text-sm font-medium text-ink">Grade and school are not recorded</p>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3">
                                <p class="text-sm font-semibold text-ink">
                                    {{ $learner['logins_7d'] }} {{ $learner['logins_7d'] === 1 ? 'login' : 'logins' }} · {{ $learner['duration_7d'] }}
                                </p>
                                <a href="{{ route('parent.home', ['child' => $dashboard['active_child'], 'term' => $selectedTerm, 'learner' => $learner['id']]) }}"
                                    @if ($isSelected) aria-current="page" @endif
                                    class="text-sm font-semibold text-ink underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-blue">
                                    {{ $isSelected ? 'Viewing telemetry ✓' : 'Inspect learner →' }}
                                </a>
                            </div>
                            <p class="mt-2 text-[11px] text-muted">Sign-in and observed session span over the last 7 days; not study time.</p>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">No student account is linked yet. During student registration, enter this verified parent account’s email to enable access to that student’s session records.</p>
            @endif
        </section>

        @if ($selectedLearner)
            <section aria-labelledby="active-telemetry-heading" class="{{ $card }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-lg border-2 border-black bg-ink px-3 py-1 text-xs font-semibold uppercase tracking-wide text-card">Active telemetry profile</span>
                        <h2 id="active-telemetry-heading" class="text-xl font-semibold text-ink">{{ $selectedLearner['name'] }}</h2>
                        <span class="rounded-full border-2 border-black bg-card-2 px-3 py-1 text-sm font-semibold text-ink">{{ $selectedLearner['status_label'] }}</span>
                    </div>
                    <p class="text-sm text-muted">{{ $selectedLearner['email'] }}</p>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <x-stat-tile label="Total logins this week" :value="$selectedLearner['logins_7d']" sub="Recorded portal sessions" />
                    <x-stat-tile label="Observed session span" :value="$selectedLearner['duration_7d']" sub="Across sessions started in the last 7 days · not study time" tone="sky" />
                    <x-stat-tile label="Last active & device" :value="$selectedLearner['last_active_label'] ?? 'No activity recorded'" :sub="$selectedLearner['latest_browser'] ? $selectedLearner['latest_browser'].' · '.$selectedLearner['latest_platform'] : 'Device details unavailable'" />
                    <x-stat-tile label="Recorded visits" :value="$selectedLearner['recorded_visits']" sub="Successful dashboard/report views" tone="peach" />
                </div>
                <p class="mt-3 text-xs text-muted">School, grade, subject averages, study actions, and educator remarks are not linked to this account. This activity log does not collect IP addresses or locations.</p>
            </section>
        @endif

        <section aria-labelledby="linked-learners-heading" class="{{ $card }}">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="linked-learners-heading" class="text-xl font-semibold text-ink">Linked learner sessions</h2>
                    <p class="mt-1 text-sm text-muted">Real sessions and student dashboard/report visits from accounts linked to this verified parent.</p>
                </div>
                <span class="{{ $soon }}">90-day retention · no IP/location in this log</span>
            </div>
            @if ($selectedLearner)
                <div class="mt-4 grid gap-5 xl:grid-cols-2">
                    @php($learner = $selectedLearner)
                        <article id="learner-{{ $learner['id'] }}-activity" class="scroll-mt-6 overflow-hidden rounded-2xl border-2 border-black bg-card">
                            <header class="flex flex-wrap items-center justify-between gap-3 border-b-2 border-black bg-sky p-4">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-ink">{{ $learner['name'] }}</h3>
                                    <p class="mt-0.5 truncate text-xs text-muted">{{ $learner['email'] }}</p>
                                </div>
                                <span class="rounded-full border-2 border-black bg-white px-2.5 py-0.5 text-xs font-semibold text-ink">{{ $learner['logins_7d'] }} sign-ins · 7 days</span>
                            </header>
                            <div class="p-4">
                                @if (count($learner['sessions']) > 0)
                                    <div class="space-y-3">
                                        @foreach ($learner['sessions'] as $session)
                                            <section class="overflow-hidden rounded-xl border-2 border-black bg-card-2">
                                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line p-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-ink">{{ $session['date_label'] }} · Login {{ $session['login_label'] }} · {{ $session['end_label'] }}</p>
                                                        <p class="mt-1 text-xs text-muted">{{ $session['browser'] }} · {{ $session['platform'] }} · {{ $session['duration_label'] }} observed session span</p>
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
                                    <p class="mt-3 text-xs text-muted">Session span is measured between login and logout or the last recorded request; it is not study time. Only successful student dashboard/report visits are listed. This activity log does not include IP addresses or locations.</p>
                                @else
                                    <p class="text-sm text-muted">No student sessions have been recorded in the last 90 days.</p>
                                @endif
                                @if ($learner['last_active'])
                                    <p class="mt-3 text-xs text-muted">Last recorded request: {{ $learner['last_active'] }}.</p>
                                @endif
                            </div>
                        </article>
                </div>
            @else
                <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">Link a student account to see its recorded login sessions and dashboard/report visits here.</p>
            @endif
        </section>

        <section aria-labelledby="children-heading" class="{{ $card }}">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 id="children-heading" class="text-lg font-normal text-ink">Sample academic reports</h2>
                    <p class="mt-1 text-sm text-muted">These sample reports demonstrate term-based marks and assessment activity; they are not linked to the student accounts above.</p>
                </div>
                <span class="{{ $soon }}">{{ count($dashboard['child_cards']) }} profiles</span>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ($dashboard['child_cards'] as $childKey => $profile)
                    @php($isSelected = $childKey === $dashboard['active_child'])
                    <a href="{{ route('parent.home', ['child' => $childKey, 'term' => $selectedTerm]) }}"
                        @if ($isSelected) aria-current="true" @endif
                        class="gg-brutal-card flex items-center justify-between gap-4 rounded-2xl border-2 border-black p-4 transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-blue {{ $isSelected ? 'bg-butter' : 'bg-card-2' }}">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-white font-semibold text-lg text-ink">{{ strtoupper(substr($profile['name'], 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-ink">{{ $profile['name'] }}</p>
                                <p class="mt-0.5 text-xs text-muted">Grade {{ $profile['grade'] }} · {{ $profile['term'] }}</p>
                                <p class="mt-1 text-xs text-ink">{{ $profile['average'] }}% average · {{ $profile['at_target'] }}/{{ $profile['subject_count'] }} subjects at goal</p>
                            </div>
                        </div>
                        <span class="shrink-0 text-xs font-semibold text-ink">{{ $isSelected ? 'Viewing' : 'View profile' }} @include('partials.icon', ['name' => 'arrow-right', 'size' => 14])</span>
                    </a>
                @endforeach
            </div>
        </section>

        <header class="{{ $card }} flex flex-wrap items-center justify-between gap-4 bg-butter">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white font-semibold text-2xl text-ink">{{ strtoupper(substr($child['name'], 0, 1)) }}</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Selected learner · Grade {{ $child['grade'] }} · {{ $snap['term'] }}</p>
                    <h2 class="mt-1 text-3xl font-normal tracking-tight text-ink sm:text-4xl">{{ $child['name'] }}'s progress</h2>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-ink">
                    <span class="text-muted">Subjects at goal</span>
                    <span class="font-semibold text-ink">{{ $snap['at_target'] }} of {{ $snap['subject_count'] }}</span>
                </span>
            </div>
        </header>
        <p class="rounded-xl border border-line bg-card-2 p-3 text-sm text-muted">The term selector updates marks and averages. Assessment activity and topic mastery are not available by term in this sample; the progress chart continues to show every term.</p>

        <div class="grid gap-5 lg:grid-cols-12">
            {{-- 1. The answer first --}}
            <section aria-labelledby="summary-heading" class="relative overflow-hidden {{ $card }} lg:col-span-12">
                <div class="relative">
                    <h2 id="summary-heading" class="sr-only">Summary</h2>
                    <p class="text-xl leading-snug text-ink sm:text-2xl font-normal">{{ $dashboard['summary'] }}</p>
                    @if ($dashboard['next_step'])
                        <div class="mt-4 rounded-xl border-2 border-black bg-butter p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink">Suggested next step</p>
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

            <section aria-labelledby="activity-overview-heading" class="{{ $card }} lg:col-span-12">
                <div>
                    <h2 id="activity-overview-heading" class="text-lg font-normal text-ink">Learning activity overview</h2>
                    <p class="mt-1 text-sm text-muted">Recent activity is based on scored quizzes and past papers.</p>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <x-stat-tile label="Previous 30 days" :value="$act['completed_prev_30d']" sub="Recorded assessments" compact />
                    <x-stat-tile label="Change vs previous 30 days" :value="$pts($act['delta'])" :tone="$act['delta'] > 0 ? 'good' : ($act['delta'] < 0 ? 'low' : 'default')" sub="Compared with the latest 30 days" compact />
                    <x-stat-tile label="Active weeks" :value="$act['weeks_active'].' / '.$act['weeks_total']" sub="Weeks with a recorded assessment" compact />
                    <x-stat-tile label="Last recorded activity" :value="$act['last_ago'] ?? 'No activity'" :sub="$act['last_label'] ?? 'No assessment date recorded'" compact />
                </div>
                <p class="mt-3 text-xs text-muted">These sample academic profiles have no linked login or device records. Real linked-account sessions are shown above; study time and educator remarks are not recorded.</p>
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
            <section aria-labelledby="support-heading" class="{{ $card }} lg:col-span-5">
                <h2 id="support-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                    Subject performance and learning support
                </h2>
                <p class="mt-1 text-sm text-muted">Marks, goals, and practice suggestions from the selected learner report.</p>
                @if (count($dashboard['subjects']) > 0)
                    <ul class="mt-4 space-y-3">
                        @foreach ($dashboard['subjects'] as $s)
                            <li class="rounded-xl border border-line bg-card-2 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium text-ink">{{ $s['name'] }}</span>
                                        <span class="tabular-nums text-ink font-semibold">{{ $s['current'] }}%</span>
                                    </div>
                                    <span class="text-xs font-semibold {{ $text[$s['target_status']['tone']] }}">Goal {{ $s['target'] }}% · {{ $s['target_status']['detail'] }}</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-line/40">
                                    <div class="h-full rounded-full bg-brand" style="width: {{ min(100, max(0, $s['current'])) }}%"></div>
                                </div>
                                @if ($s['weakest'])
                                    <p class="mt-2 text-xs text-muted">Topic to practise: <span class="text-ink font-medium">{{ $s['weakest']['name'] }}</span> ({{ $s['weakest']['mastery'] }}% mastery)</p>
                                @endif
                                <p class="mt-2 text-xs text-ink-sub bg-card/60 rounded-lg p-2.5 border border-line/60">{{ $s['home_steps'][0] }}</p>
                                <a href="{{ $reportUrl }}#subject-{{ $s['key'] }}" class="mt-3 {{ $link }}">View detail<span class="sr-only"> for {{ $s['name'] }}</span> @include('partials.icon', ['name' => 'arrow-right', 'size' => 14])</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">No subject marks are available for this report.</p>
                @endif
                <p class="mt-3 text-xs text-muted">Educator remarks are not recorded in this demo; suggestions use available marks and topic mastery.</p>
            </section>

            {{-- 6. Recorded assessments grouped by their available completion date --}}
            <section aria-labelledby="activity-heading" class="{{ $card }} lg:col-span-7">
                <h2 id="activity-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'zap', 'size' => 20])
                    Recorded learning activity
                </h2>
                <p class="mt-1 text-sm text-muted">Recent quizzes and past papers grouped by recorded completion date.</p>
                @if (count($dashboard['activity_groups']) > 0)
                    <div class="mt-4 space-y-3">
                        @foreach ($dashboard['activity_groups'] as $group)
                            <article class="overflow-hidden rounded-2xl border-2 border-black bg-card-2">
                                <header class="flex flex-wrap items-center justify-between gap-2 border-b-2 border-black px-4 py-3">
                                    <h3 class="font-semibold text-ink">{{ $group['date_label'] }}</h3>
                                    <span class="rounded-full border border-line bg-card px-2.5 py-0.5 text-xs font-semibold text-ink">{{ count($group['actions']) }} {{ count($group['actions']) === 1 ? 'assessment' : 'assessments' }}</span>
                                </header>
                                <ul class="divide-y divide-line/70 px-4">
                                    @foreach ($group['actions'] as $row)
                                        <li class="flex items-center justify-between gap-3 py-3">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="rounded-full border border-line bg-card px-2 py-0.5 text-[11px] font-semibold text-ink">{{ $row['type_label'] }}</span>
                                                    <span class="truncate text-xs text-muted">{{ $row['subject'] }}</span>
                                                </div>
                                                <p class="mt-1 truncate font-medium text-ink text-sm">{{ $row['title'] }}</p>
                                            </div>
                                            <span class="shrink-0 text-base font-semibold tabular-nums {{ $text[$row['tone']] }}">{{ $row['score'] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-muted">Quizzes and past papers will appear here once {{ $child['first_name'] }} completes them.</p>
                @endif
                <p class="mt-3 text-xs text-muted">This timeline contains assessment records only; login times, devices, and session duration are not tracked.</p>
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
