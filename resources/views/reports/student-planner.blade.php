@extends('layouts.student', ['title' => 'Study Planner', 'active' => 'planner'])

@php
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $button = 'inline-flex items-center rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-blue focus-visible:outline-2 focus-visible:outline-blue planner-btn';
    $buttonDone = $button . ' planner-btn-done';
    $buttonSkip = $button . ' planner-btn-skip';
    $buttonUndo = $button . ' planner-btn-undo';
    $buttonRemove = $button . ' planner-btn-remove';
@endphp

@section('student-content')
    <div class="space-y-5">
        {{-- Parent Word of Encouragement Banner --}}
        @if (isset($encouragement) && $encouragement)
            <div class="rounded-2xl border-2 border-brand/40 bg-brand/10 p-4 sm:p-5 flex items-start gap-3.5 shadow-xs">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-brand/50 bg-brand/20 text-brand">
                    @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Word of encouragement from {{ $encouragement->parent->name }}</p>
                        <span class="text-[11px] text-muted">{{ $encouragement->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1 text-base font-medium text-ink">"{{ $encouragement->message }}"</p>
                </div>
            </div>
        @endif

        {{-- Top Header --}}
        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-3xl font-normal tracking-tight text-ink sm:text-4xl">Study planner</h1>
                <p class="mt-2 text-muted">Grade {{ $student['grade'] }} · Your revision sessions for the week ahead</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="document.getElementById('add-session-panel').classList.toggle('hidden')" class="{{ $button }}">
                    + Add Session
                </button>
                <form method="POST" action="{{ route('student.planner.generate') }}">
                    @csrf
                    <button type="submit" class="{{ $button }}">{{ count($days) > 0 ? 'Rebuild my plan' : 'Build my plan' }}</button>
                </form>
            </div>
        </header>

        {{-- Status / Error Flash Messages --}}
        @if (session('status'))
            <p role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-700/60 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p role="alert" class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 dark:border-red-700/60 dark:bg-red-950/40 dark:text-red-300">{{ session('error') }}</p>
        @endif

        {{-- Progress Header --}}
        <section aria-label="Study progress summary" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-xl border border-line bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Today's Progress</p>
                <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $progress['today_done'] }} / {{ $progress['today_total'] }} done</p>
                <p class="mt-1 text-xs text-muted">Sessions completed today</p>
            </div>
            <div class="rounded-xl border border-line bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">This Week's Progress</p>
                <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $progress['week_done'] }} / {{ $progress['week_total'] }} done</p>
                <p class="mt-1 text-xs text-muted">Counted sessions this week</p>
            </div>
            <div class="rounded-xl border border-line bg-card p-4 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Study Minutes</p>
                <p class="mt-2 text-2xl font-bold text-ink tabular-nums">{{ $progress['minutes_done'] }}m <span class="text-sm font-normal text-muted">/ {{ $progress['minutes_planned'] }}m</span></p>
                <p class="mt-1 text-xs text-muted">Actual study time completed</p>
            </div>
        </section>

        {{-- Add Session Collapsible Panel --}}
        <div id="add-session-panel" class="hidden rounded-2xl border-2 border-brand/30 bg-card p-5 shadow-sm">
            <div class="flex items-center justify-between gap-2 border-b border-line pb-3">
                <h3 class="font-semibold text-ink text-base">Add revision session</h3>
                <button type="button" onclick="document.getElementById('add-session-panel').classList.add('hidden')" class="text-xs text-muted hover:text-ink">Close ✕</button>
            </div>
            <form method="POST" action="{{ route('student.planner.store') }}" class="mt-4 grid gap-4 sm:grid-cols-4">
                @csrf
                <div>
                    <label for="session-subject" class="block text-xs font-semibold uppercase tracking-wider text-muted">Subject</label>
                    <select id="session-subject" name="subject_key" required class="mt-1 w-full rounded-lg border border-line bg-card-2 p-2 text-sm text-ink focus:border-brand">
                        @foreach ($subjects as $sub)
                            <option value="{{ $sub['key'] }}">{{ $sub['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="session-topic" class="block text-xs font-semibold uppercase tracking-wider text-muted">Topic</label>
                    <input type="text" id="session-topic" name="topic" placeholder="e.g. Probability, Calculus" required maxlength="120" class="mt-1 w-full rounded-lg border border-line bg-card-2 p-2 text-sm text-ink focus:border-brand">
                </div>
                <div>
                    <label for="session-minutes" class="block text-xs font-semibold uppercase tracking-wider text-muted">Duration</label>
                    <select id="session-minutes" name="minutes" required class="mt-1 w-full rounded-lg border border-line bg-card-2 p-2 text-sm text-ink focus:border-brand">
                        <option value="15">15 min</option>
                        <option value="30" selected>30 min</option>
                        <option value="45">45 min</option>
                        <option value="60">60 min</option>
                    </select>
                </div>
                <div>
                    <label for="session-date" class="block text-xs font-semibold uppercase tracking-wider text-muted">Date</label>
                    <input type="date" id="session-date" name="planned_date" min="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-line bg-card-2 p-2 text-sm text-ink focus:border-brand">
                </div>
                <div class="sm:col-span-4 flex justify-end">
                    <button type="submit" class="gg-button gg-button-primary">Save Session</button>
                </div>
            </form>
        </div>

        {{-- Main Study Plan Card --}}
        <section aria-labelledby="plan-heading" class="{{ $card }}">
            <h2 id="plan-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                Your plan
            </h2>
            <p class="mt-1 text-sm text-muted">
                Built from your <a href="{{ route('student.recommendations') }}" class="underline hover:text-ink">learning recommendations</a>:
                highest priority first, {{ $rules['session_minutes'] }}-minute sessions, up to {{ $rules['daily_minutes'] }} minutes a day over {{ $rules['plan_days'] }} days.
                Topics are planned before the exam for that subject. Rebuilding keeps sessions you already did or skipped.
            </p>

            @forelse ($days as $day)
                <div class="mt-6 first:mt-4">
                    <div class="flex items-center justify-between border-b border-line pb-1.5">
                        <h3 class="text-sm font-semibold uppercase tracking-wider {{ $day['is_today'] ? 'text-ink font-bold' : 'text-muted' }}">
                            {{ $day['label'] }}
                        </h3>
                        @if ($day['is_today'])
                            <span class="rounded-full bg-brand/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand">Current Day</span>
                        @elseif ($day['is_future'])
                            <span class="text-[11px] text-muted">Upcoming</span>
                        @endif
                    </div>

                    <ul class="mt-3 space-y-2.5">
                        @foreach ($day['sessions'] as $session)
                            @php
                                $isDone = $session['status'] === 'done';
                                $isSkipped = $session['status'] === 'skipped';
                                $isTooShort = $session['status'] === 'too_short';
                                $isPlanned = $session['status'] === 'planned';
                                $isFuture = $session['is_future'];
                            @endphp
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-card-2 p-3.5" data-session-card="{{ $session['id'] }}">
                                <div class="min-w-0 flex-1">
                                    {{-- Topic with status-appropriate styling --}}
                                    @if ($isDone)
                                        <p class="font-medium line-through text-emerald-800 dark:text-emerald-400 opacity-90">{{ $session['topic'] }}</p>
                                    @elseif ($isSkipped)
                                        <p class="font-medium text-muted">{{ $session['topic'] }}</p>
                                    @elseif ($isTooShort)
                                        <p class="font-medium text-red-800 dark:text-red-400">{{ $session['topic'] }}</p>
                                    @else
                                        <p class="font-medium text-ink">{{ $session['topic'] }}</p>
                                    @endif

                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted planner-session-meta">
                                        <span>{{ $session['subject'] }} · {{ $session['minutes'] }} min</span>

                                        @if ($isDone)
                                            <span class="planner-status-label planner-status-done">Done ({{ $session['actual_minutes'] }} min)</span>
                                        @elseif ($isSkipped)
                                            <span class="planner-status-label planner-status-skipped">Skipped</span>
                                        @elseif ($isTooShort)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800 border border-red-300 dark:bg-red-950 dark:border-red-800 dark:text-red-300">
                                                Marked Done in {{ $session['actual_minutes'] }} min · Not Counted
                                            </span>
                                        @elseif ($isFuture)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700 border border-slate-300 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                                Scheduled
                                            </span>
                                        @endif

                                        {{-- Live Active Timer on Started Planned Session --}}
                                        @if ($isPlanned && ! $isFuture && $session['started_at'])
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-400 bg-blue-50 px-2.5 py-0.5 font-mono text-xs font-bold text-blue-900 dark:bg-blue-950/60 dark:border-blue-700 dark:text-blue-200">
                                                <span class="size-2 rounded-full {{ $session['is_paused'] ? 'bg-amber-500' : 'animate-ping bg-blue-500' }}"></span>
                                                <span class="live-timer" data-session-id="{{ $session['id'] }}" data-started-at="{{ $session['started_at'] }}" data-is-paused="{{ $session['is_paused'] ? '1' : '0' }}" data-paused-seconds="{{ $session['paused_seconds'] }}" data-min-minutes="{{ $session['minimum_minutes'] }}">
                                                    00:00
                                                </span>
                                                <span class="text-[10px] text-blue-700 dark:text-blue-300 font-sans font-normal">(Min {{ $session['minimum_minutes'] }}m)</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($isFuture)
                                        {{-- Future sessions cannot be marked Done/Skip before their date --}}
                                        <form method="POST" action="{{ route('student.planner.destroy', $session['id']) }}" onsubmit="return confirm('Are you sure you want to remove this scheduled session?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $buttonRemove }}" aria-label="Remove {{ $session['topic'] }} session">Remove</button>
                                        </form>
                                    @elseif ($isPlanned)
                                        @if (! $session['started_at'])
                                            {{-- Start Session Button --}}
                                            <form method="POST" action="{{ route('student.planner.start', $session['id']) }}">
                                                @csrf
                                                <button type="submit" class="{{ $button }} bg-brand/10 text-brand border-brand/40 hover:bg-brand hover:text-white">Start</button>
                                            </form>
                                            {{-- Skip Session Button --}}
                                            <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="skipped">
                                                <button type="submit" class="{{ $buttonSkip }}">Skip</button>
                                            </form>
                                        @else
                                            {{-- Running Session: Pause/Resume + Done --}}
                                            @if ($session['is_paused'])
                                                <form method="POST" action="{{ route('student.planner.resume', $session['id']) }}">
                                                    @csrf
                                                    <button type="submit" class="{{ $button }}">Resume</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('student.planner.pause', $session['id']) }}">
                                                    @csrf
                                                    <button type="submit" class="{{ $button }}">Pause</button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="done">
                                                <button type="submit" class="{{ $buttonDone }}" data-done-btn="{{ $session['id'] }}" title="Requires at least {{ $session['minimum_minutes'] }} minutes of study">Done</button>
                                            </form>

                                            <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="skipped">
                                                <button type="submit" class="{{ $buttonSkip }}">Skip</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('student.planner.destroy', $session['id']) }}" onsubmit="return confirm('Are you sure you want to remove this session?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $buttonRemove }}" aria-label="Remove {{ $session['topic'] }} session">Remove</button>
                                        </form>
                                    @elseif ($isSkipped)
                                        {{-- Skipped: Reschedule + Undo + Remove --}}
                                        <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="reschedule">
                                            <button type="submit" class="{{ $button }} border-amber-300 bg-amber-50 text-amber-900 hover:bg-amber-100 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-200">Reschedule</button>
                                        </form>
                                        <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="planned">
                                            <button type="submit" class="{{ $buttonUndo }}">Undo</button>
                                        </form>
                                        <form method="POST" action="{{ route('student.planner.destroy', $session['id']) }}" onsubmit="return confirm('Are you sure you want to remove this session?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $buttonRemove }}" aria-label="Remove {{ $session['topic'] }} session">Remove</button>
                                        </form>
                                    @else
                                        {{-- Done or Too Short: Undo + Remove --}}
                                        <form method="POST" action="{{ route('student.planner.update', $session['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="planned">
                                            <button type="submit" class="{{ $buttonUndo }}">Undo</button>
                                        </form>
                                        <form method="POST" action="{{ route('student.planner.destroy', $session['id']) }}" onsubmit="return confirm('Are you sure you want to remove this session?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $buttonRemove }}" aria-label="Remove {{ $session['topic'] }} session">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">You have no sessions planned yet. Choose "Build my plan" and the weakest topics are spread over the next {{ $rules['plan_days'] }} days.</p>
            @endforelse
        </section>
    </div>

    {{-- Live Timer JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timerElements = document.querySelectorAll('.live-timer');
            if (timerElements.length === 0) return;

            function updateTimers() {
                const now = Date.now();
                timerElements.forEach(el => {
                    const startedAt = new Date(el.dataset.startedAt).getTime();
                    const isPaused = el.dataset.isPaused === '1';
                    const pausedSeconds = parseInt(el.dataset.pausedSeconds || '0', 10);
                    const minMinutes = parseInt(el.dataset.minMinutes || '0', 10);
                    const sessionId = el.dataset.sessionId;
                    const doneBtn = document.querySelector(`[data-done-btn="${sessionId}"]`);

                    if (isNaN(startedAt)) return;

                    let elapsedTotalSeconds = Math.max(0, Math.floor((now - startedAt) / 1000));
                    let activeSeconds = Math.max(0, elapsedTotalSeconds - pausedSeconds);

                    const mins = Math.floor(activeSeconds / 60);
                    const secs = activeSeconds % 60;
                    el.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

                    // Update Done button disabled state based on minimum time requirement
                    if (doneBtn) {
                        const minSecs = minMinutes * 60;
                        if (activeSeconds < minSecs) {
                            doneBtn.disabled = true;
                            doneBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            doneBtn.title = `Study at least ${minMinutes}m (${minMinutes - mins}m remaining)`;
                        } else {
                            doneBtn.disabled = false;
                            doneBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            doneBtn.title = 'Complete session';
                        }
                    }
                });
            }

            updateTimers();
            setInterval(updateTimers, 1000);
        });
    </script>
@endsection
