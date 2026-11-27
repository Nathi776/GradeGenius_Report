@extends('layouts.parent', ['title' => 'Parent dashboard', 'active' => 'home'])

@php
    $child = $dashboard['child'];
    $snap = $dashboard['snapshot'];
    $act = $dashboard['activity'];
    $guardian = auth()->user();
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad', 'sky' => 'text-sky'];
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
    $pts = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')).abs($n).' pts';
    $childCount = count($dashboard['children']);
    $hasMarks = $dashboard['has_marks'];
    $activeChild = $dashboard['active_child'];
    $reportUrl = route('parent.report', ['child' => $activeChild]).($selectedTerm === 'all' ? '' : '?term='.$selectedTerm);
    $activityUrl = route('parent.activity', ['child' => $activeChild]);
@endphp

@section('parent-content')
    @if ($hasMarks)
        @include('partials.print-header', ['title' => 'Parent & Guardian Academic Progress Report', 'student' => $dashboard['child'], 'term' => $dashboard['snapshot']['term']])
    @endif

    <div class="gg-portal-dashboard space-y-5">
        @if (session('status'))
            <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-bad/40 bg-bad/15 p-4 text-sm font-semibold text-ink" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if (isset($pendingChildren) && $pendingChildren->isNotEmpty())
            <section aria-labelledby="pending-requests-heading" class="gg-brutal-card rounded-3xl border-2 border-warn/80 bg-warn/10 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="pending-requests-heading" class="flex items-center gap-2 text-xl font-semibold text-ink">
                            Pending Learner Link Requests
                        </h2>
                        <p class="mt-1 text-sm text-muted">The following student accounts have requested to link with your guardian profile. Confirming allows you to monitor their academic reports and receive educator notes.</p>
                    </div>
                    <span class="rounded-full border border-line bg-card-2 px-3 py-1 text-xs font-semibold text-ink">{{ $pendingChildren->count() }} Pending</span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach ($pendingChildren as $pending)
                        <div class="flex flex-col justify-between gap-3 rounded-2xl border-2 border-line bg-card p-4 sm:flex-row sm:items-center">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-line bg-lavender font-semibold text-base text-ink">
                                    {{ strtoupper(substr($pending->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-ink">{{ $pending->name }}</h3>
                                    <p class="truncate text-xs text-muted">{{ $pending->email }}</p>
                                    <span class="mt-1 inline-block text-[11px] font-medium text-warn">Awaiting your approval</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <form method="POST" action="{{ route('parent.links.confirm', $pending->id) }}">
                                    @csrf
                                    <button type="submit" class="gg-button gg-button-primary px-3 py-1.5 text-xs font-semibold">
                                        Confirm Link
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('parent.links.reject', $pending->id) }}" onsubmit="return confirm('Are you sure you want to decline this link request?');">
                                    @csrf
                                    <button type="submit" class="gg-button gg-button-surface px-3 py-1.5 text-xs font-semibold text-muted hover:text-bad">
                                        Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <header class="{{ $card }} flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-lavender font-semibold text-2xl text-black">{{ strtoupper(substr(trim($guardian->name), 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Parent portal · Guardian view</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <h1 class="break-words text-3xl font-semibold tracking-tight text-ink sm:text-4xl">{{ $guardian->name }}</h1>
                        @if ($guardian->hasVerifiedEmail())
                            <span class="rounded-full border border-line bg-good/20 px-2.5 py-0.5 text-xs font-semibold text-ink">Verified guardian account</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-muted">{{ $childCount }} {{ $childCount === 1 ? 'confirmed child' : 'confirmed children' }}</p>
                </div>
            </div>
        </header>

        @if ($childCount > 1)
            <nav aria-label="Switch child" class="flex flex-wrap gap-2">
                @foreach ($dashboard['child_cards'] as $childKey => $profile)
                    @php($isSelected = (string) $childKey === (string) $activeChild)
                    <a href="{{ route('parent.home', ['child' => $childKey, 'term' => $selectedTerm]) }}" @if ($isSelected) aria-current="true" @endif
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-blue {{ $isSelected ? 'border-brand/50 bg-brand/15 text-ink' : 'border-line bg-card-2 text-muted hover:text-ink' }}">
                        <span class="font-semibold text-ink">{{ $profile['name'] }}</span>
                        <span class="text-xs">{{ $profile['has_marks'] ? $profile['average'].'% average' : 'No marks yet' }}</span>
                    </a>
                @endforeach
            </nav>
        @endif

        @if (! $hasMarks)
            <section aria-labelledby="no-marks-heading" class="{{ $card }}">
                <h2 id="no-marks-heading" class="text-2xl font-normal text-ink">{{ $child['name'] }}</h2>
                <p class="mt-2 text-base text-ink">No marks have been recorded for {{ $child['first_name'] }} yet.</p>
                <p class="mt-1 text-sm text-muted">Reports appear here once {{ $child['first_name'] }}'s teacher records quiz or past-paper results. Nothing is estimated in the meantime.</p>
            </section>
        @else
        <header class="{{ $card }} flex flex-wrap items-center justify-between gap-4 bg-butter">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white font-semibold text-2xl text-ink">{{ strtoupper(substr($child['name'], 0, 1)) }}</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Selected learner · Grade {{ $child['grade'] }} · {{ $snap['term'] }}</p>
                    <h2 class="mt-1 break-words text-3xl font-normal tracking-tight text-ink sm:text-4xl">{{ $child['name'] }}'s progress</h2>
                </div>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-ink">
                <span class="text-muted">Subjects at goal</span>
                <span class="font-semibold text-ink">{{ $snap['at_target'] }} of {{ $snap['subject_count'] }}</span>
            </span>
        </header>

        <div class="grid gap-5 lg:grid-cols-12">
            <section aria-labelledby="summary-heading" class="relative overflow-hidden {{ $card }} lg:col-span-12">
                <h2 id="summary-heading" class="sr-only">Summary</h2>
                <p class="text-xl leading-snug text-ink sm:text-2xl font-normal">{{ $dashboard['summary'] }}</p>
                @if ($dashboard['next_step'])
                    <div class="mt-4 rounded-xl border-2 border-black bg-butter p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink">Suggested next step</p>
                        <p class="mt-1 text-ink font-medium text-sm">{{ $dashboard['next_step'] }}</p>
                    </div>
                @endif
            </section>

            <section aria-label="Academic snapshot" class="grid grid-cols-2 gap-3 lg:col-span-12 lg:grid-cols-4">
                <x-stat-tile label="Current average" :value="$snap['average'].'%'" :sub="'Level '.$snap['level'].' of 7 · '.$snap['term']" />
                @if ($snap['has_comparison'])
                    <x-stat-tile label="Change" :value="$pts($snap['change'])" :sub="'Since '.$snap['first_term']" :tone="$snap['change'] > 0 ? 'good' : ($snap['change'] < 0 ? 'low' : 'default')" />
                @else
                    <x-stat-tile label="Change" value="Not yet" sub="No earlier recorded term to compare with" />
                @endif
                <x-stat-tile label="Subjects at goal" :value="$snap['at_target'].' of '.$snap['subject_count']" sub="Goal set for each subject" tone="peach" />
                <x-stat-tile label="Completed recently" :value="$act['completed_30d']" :sub="'quizzes and past papers, last 30 days · '.$act['delta_label'].' vs the 30 days before'" tone="sky" />
            </section>

            <section aria-labelledby="progress-heading" class="{{ $card }} lg:col-span-7">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 id="progress-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'trending-up', 'size' => 20])
                        Academic progress
                    </h2>
                    <a href="{{ $reportUrl }}" class="{{ $link }}">Full report @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
                </div>
                <p class="mt-1 text-sm text-muted">Average across all subjects for each term with a recorded mark. @if ($snap['goal'] !== null)Average goal: {{ $snap['goal'] }}%.@endif</p>
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
                <p class="mt-1 text-sm text-muted">Marks, goals, and practice suggestions from the latest recorded results.</p>
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
                <p class="mt-3 text-xs text-muted">Suggestions use recorded marks and topic mastery.</p>
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
            </section>

        </div>
        @endif

        @if ($learner !== null)
            <div class="grid gap-5 lg:grid-cols-2">
                <section aria-labelledby="sharing-heading" class="{{ $card }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 id="sharing-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                            @include('partials.icon', ['name' => 'zap', 'size' => 20])
                            Learner activity
                        </h2>
                        <a href="{{ $activityUrl }}" class="text-xs font-semibold text-ink underline underline-offset-2">{{ $learner['sharing'] ? 'View sessions →' : 'Details →' }}</a>
                    </div>
                    @if ($learner['sharing'])
                        <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-xl border border-line bg-card-2 p-3"><dt class="text-[11px] uppercase tracking-wider text-muted">Logins, 7 days</dt><dd class="mt-1 text-lg font-semibold text-ink">{{ $learner['logins_7d'] }}</dd></div>
                            <div class="rounded-xl border border-line bg-card-2 p-3"><dt class="text-[11px] uppercase tracking-wider text-muted">Last active</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $learner['last_active'] ?? 'No activity yet' }}</dd></div>
                            <div class="rounded-xl border border-line bg-card-2 p-3"><dt class="text-[11px] uppercase tracking-wider text-muted">Status</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $learner['status_label'] }}</dd></div>
                        </dl>
                    @else
                        <p class="mt-3 text-sm text-ink">{{ $child['first_name'] }} has not shared activity with you.</p>
                        <p class="mt-1 text-sm text-muted">Marks and reports are shown either way. Learners choose whether to share when they were online in their own Settings.</p>
                    @endif
                </section>

                <section aria-labelledby="planner-heading" class="{{ $card }}">
                    <div class="flex items-center justify-between gap-2">
                        <h2 id="planner-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                            @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                            Study planner
                        </h2>
                        <a href="{{ route('parent.planner', ['child_id' => $activeChild]) }}" class="shrink-0 text-xs font-semibold text-ink underline underline-offset-2">View full plan →</a>
                    </div>
                    @if ($learner['sharing'] && $learner['study'] !== null)
                        <div class="mt-4 space-y-2.5">
                            <div class="flex items-center justify-between rounded-xl border border-line bg-card-2 p-3">
                                <span class="text-xs text-muted">Study streak</span>
                                <span class="text-sm font-semibold text-ink">🔥 {{ $learner['study']['streak'] }} {{ $learner['study']['streak'] === 1 ? 'day' : 'days' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-line bg-card-2 p-3">
                                <span class="text-xs text-muted">Weekly goal</span>
                                <span class="text-sm font-semibold text-ink">{{ $learner['study']['weekly_minutes'] }} / {{ $learner['study']['weekly_goal'] }} min</span>
                            </div>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-muted">See the revision sessions planned for {{ $child['first_name'] }} and send encouragement. Study time and streak show here when {{ $child['first_name'] }} shares activity.</p>
                    @endif
                </section>
            </div>
        @endif
    </div>
@endsection
