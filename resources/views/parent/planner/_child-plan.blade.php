@php
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
    $firstName = explode(' ', trim($child->name))[0];
@endphp

<div class="space-y-5">
    {{-- Child Header Card --}}
    <section class="{{ $card }} flex flex-wrap items-center justify-between gap-4 bg-butter">
        <div class="flex items-center gap-4">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white font-semibold text-2xl text-ink">
                {{ strtoupper(substr(trim($child->name), 0, 1)) }}
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Learner Study Planner · Read-only view</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">{{ $child->name }}'s Revision Plan</h2>
                <p class="mt-1 text-xs text-muted">Grade and school are not recorded</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border-2 border-black bg-white px-3 py-1 text-xs font-semibold text-ink">
                🔥 {{ $stats['streak'] }} {{ $stats['streak'] === 1 ? 'day' : 'days' }} streak
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border-2 border-black bg-white px-3 py-1 text-xs font-semibold text-ink">
                {{ $stats['minutes_completed'] }} / {{ $stats['weekly_goal'] }} min weekly goal
            </span>
        </div>
    </section>

    {{-- Stats Grid (Task 1 & Task 6) --}}
    <section aria-label="Study progress summary" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">To Do</p>
            <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $stats['planned'] }}</p>
            <p class="mt-1 text-xs text-muted">Upcoming planned</p>
        </div>
        <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-good">Done this week</p>
            <p class="mt-2 text-2xl font-bold text-good tabular-nums">{{ $stats['done_this_week'] }}</p>
            <p class="mt-1 text-xs text-muted">Counted sessions</p>
        </div>
        <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-warn">Skipped</p>
            <p class="mt-2 text-2xl font-bold text-warn tabular-nums">{{ $stats['skipped'] }}</p>
            <p class="mt-1 text-xs text-muted">This week</p>
        </div>
        <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Planned</p>
            <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $stats['minutes_planned'] }}m</p>
            <p class="mt-1 text-xs text-muted">Total allocated</p>
        </div>
        <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-sky">Real Study</p>
            <p class="mt-2 text-2xl font-bold text-sky tabular-nums">{{ $stats['minutes_completed'] }}m</p>
            <p class="mt-1 text-xs text-muted">Actually completed</p>
        </div>
        <div class="rounded-2xl border-2 border-black bg-card p-4 shadow-xs">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Remaining</p>
            <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $stats['minutes_remaining'] }}m</p>
            <p class="mt-1 text-xs text-muted">Planned to finish</p>
        </div>
    </section>

    {{-- Sessions Timeline Grouped by Day (Task 1 & Task 5 & Task 9) --}}
    <section aria-labelledby="sessions-heading" class="{{ $card }}">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
            <div>
                <h2 id="sessions-heading" class="text-xl font-semibold text-ink">Revision sessions by day</h2>
                <p class="mt-1 text-sm text-muted">Guardian monitoring view. Session modifications, timer controls, and plan generation are reserved for the learner.</p>
            </div>
            <span class="rounded-full border border-line bg-card-2 px-3 py-1 text-xs font-semibold text-muted">
                Read-only view
            </span>
        </div>

        @if (count($days) > 0)
            <div class="mt-6 space-y-6">
                @foreach ($days as $day)
                    <div class="rounded-2xl border-2 border-black bg-card p-4 sm:p-5">
                        <div class="flex items-center justify-between border-b border-line pb-2">
                            <h3 class="text-sm font-bold uppercase tracking-wider {{ $day['is_today'] ? 'text-brand' : 'text-ink' }}">
                                {{ $day['label'] }}
                            </h3>
                            @if ($day['is_today'])
                                <span class="rounded-full bg-brand/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand">
                                    Today
                                </span>
                            @elseif ($day['is_future'])
                                <span class="text-xs text-muted">Upcoming</span>
                            @endif
                        </div>

                        <ul class="mt-3 space-y-2.5">
                            @foreach ($day['sessions'] as $session)
                                @php
                                    $isDone = $session['status'] === 'done';
                                    $isSkipped = $session['status'] === 'skipped';
                                    $isTooShort = $session['status'] === 'too_short';
                                    $isFuture = $session['is_future'];
                                @endphp
                                <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-card-2 p-3.5">
                                    <div class="min-w-0 flex-1">
                                        @if ($isDone)
                                            <p class="font-medium line-through text-emerald-800 dark:text-emerald-400 opacity-90">{{ $session['topic'] }}</p>
                                        @elseif ($isSkipped)
                                            <p class="font-medium text-muted">{{ $session['topic'] }}</p>
                                        @elseif ($isTooShort)
                                            <p class="font-medium text-red-800 dark:text-red-400">{{ $session['topic'] }}</p>
                                        @else
                                            <p class="font-medium text-ink">{{ $session['topic'] }}</p>
                                        @endif

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">
                                            <span>{{ $session['subject'] }} · {{ $session['minutes'] }} min planned</span>

                                            @if ($isDone)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800 border border-emerald-300 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300">
                                                    Done ({{ $session['actual_minutes'] }} min studied)
                                                </span>
                                            @elseif ($isSkipped)
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 border border-amber-300 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-300">
                                                    Skipped
                                                </span>
                                            @elseif ($isTooShort)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-semibold text-red-800 border border-red-300 dark:bg-red-950 dark:border-red-800 dark:text-red-300 font-medium">
                                                    Marked Done in {{ $session['actual_minutes'] }} min - Not counted
                                                </span>
                                            @elseif ($isFuture)
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 border border-slate-300 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                                    Scheduled
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-semibold text-blue-800 border border-blue-300 dark:bg-blue-950 dark:border-blue-800 dark:text-blue-300">
                                                    Planned
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="font-mono text-xs text-muted">Duration: {{ $session['minutes'] }}m</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Task 9 empty state --}}
            <div class="mt-6 rounded-2xl border-2 border-dashed border-line p-8 text-center">
                <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-card-2 text-2xl" aria-hidden="true">📖</span>
                <h3 class="mt-3 text-base font-semibold text-ink">No study plan yet for this week</h3>
                <p class="mt-1 text-sm text-muted">{{ $child->name }} needs to build a plan first on their learner portal.</p>
            </div>
        @endif
    </section>

    {{-- Encouragements Section (Task 2) --}}
    <section aria-labelledby="encouragement-heading" class="{{ $card }}">
        <div class="flex items-center gap-2">
            @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
            <h2 id="encouragement-heading" class="text-xl font-semibold text-ink">Send {{ $firstName }} a word of encouragement</h2>
        </div>
        <p class="mt-1 text-sm text-muted">A supportive note appears at the top of {{ $child->name }}'s study planner to keep them motivated. Max 280 characters.</p>

        @if (session('status'))
            <p role="status" class="mt-3 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-700/60 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('parent.encouragements.store') }}" class="mt-4 space-y-3">
            @csrf
            <input type="hidden" name="student_id" value="{{ $child->id }}">
            <div>
                <label for="encouragement-message" class="sr-only">Encouragement message</label>
                <textarea id="encouragement-message" name="message" rows="3" maxlength="280" required
                    placeholder="Send {{ $firstName }} a word of encouragement..."
                    class="w-full rounded-2xl border-2 border-black bg-card-2 p-3.5 text-sm font-medium text-ink placeholder:text-muted focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"></textarea>
                <div class="mt-1 flex items-center justify-between text-xs text-muted">
                    <span>Shows as a banner on {{ $child->name }}'s planner</span>
                    <span id="char-count">Max 280 characters</span>
                </div>
            </div>
            <button type="submit" class="gg-brutal-card inline-flex items-center gap-2 rounded-xl border-2 border-black bg-butter px-4 py-2.5 text-sm font-semibold text-ink transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-blue">
                @include('partials.icon', ['name' => 'sparkles', 'size' => 16])
                Send Encouragement
            </button>
        </form>

        {{-- Encouragement History (Last 5) --}}
        <div class="mt-6 border-t border-line pt-5">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-muted">Recent encouragements sent</h3>
            @if ($encouragements->isNotEmpty())
                <ul class="mt-3 space-y-2.5">
                    @foreach ($encouragements as $enc)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-card-2 p-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink">"{{ $enc->message }}"</p>
                                <p class="mt-1 text-xs text-muted">{{ $enc->created_at->format('D j M Y · H:i') }} ({{ $enc->created_at->diffForHumans() }})</p>
                            </div>
                            @if ($enc->parent_id === auth()->id())
                                <form method="POST" action="{{ route('parent.encouragements.destroy', $enc->id) }}" onsubmit="return confirm('Delete this encouragement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700 underline focus-visible:outline-2 focus-visible:outline-red-500">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 text-sm text-muted">No encouragements sent yet. Send your first note above!</p>
            @endif
        </div>
    </section>
</div>
