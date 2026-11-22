<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">
            {{ session('status') }}
        </div>
    @endif

    {{-- Notification Center Header --}}
    <header class="gg-brutal-card flex flex-wrap items-center justify-between gap-4 rounded-3xl p-5 sm:p-6">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Notifications</h1>
                @if ($unreadCount > 0)
                    <span class="rounded-full bg-bad/20 border border-bad/40 px-2.5 py-0.5 text-xs font-bold text-bad">
                        {{ $unreadCount }} Unread
                    </span>
                @else
                    <span class="rounded-full bg-good/20 border border-good/40 px-2.5 py-0.5 text-xs font-semibold text-ink">
                        All Caught Up
                    </span>
                @endif
            </div>
            <p class="mt-1 text-sm text-muted">Academic alerts, educator notes, and important learning updates.</p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                @csrf
                <button type="submit" class="gg-button gg-button-surface inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-ink">
                    @include('partials.icon', ['name' => 'check-circle', 'size' => 15])
                    Mark all as read
                </button>
            </form>
        @endif
    </header>

    {{-- Notification List --}}
    <section aria-label="Notifications list" class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = $notification->read_at === null;
                $isMessage = isset($data['message_id']);
                $typeLabels = [
                    'progress_update' => 'Progress Update',
                    'concern' => 'Academic Concern',
                    'praise' => 'Commendation',
                    'meeting_request' => 'Consultation Request',
                    'class_announcement' => 'Class Announcement',
                ];
                $typeLabel = $typeLabels[$data['type'] ?? ''] ?? 'Notification';
                $toneClasses = match($data['type'] ?? '') {
                    'concern' => 'border-bad/40 bg-bad/10 text-bad',
                    'praise' => 'border-good/40 bg-good/10 text-good',
                    'meeting_request' => 'border-warn/40 bg-warn/10 text-warn',
                    default => 'border-brand/40 bg-brand/10 text-ink',
                };
            @endphp

            <article class="relative flex flex-col justify-between gap-4 rounded-2xl border-2 p-4 sm:flex-row sm:items-center sm:p-5 transition {{ $isUnread ? 'border-brand/60 bg-card shadow-xs' : 'border-line bg-card-2 opacity-90' }}">
                <div class="flex items-start gap-3.5 min-w-0">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-line bg-card text-ink">
                        @if ($isMessage)
                            @include('partials.icon', ['name' => 'mail', 'size' => 18])
                        @else
                            @include('partials.icon', ['name' => 'bell', 'size' => 18])
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($isUnread)
                                <span class="size-2 rounded-full bg-blue" title="Unread"></span>
                            @endif
                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $toneClasses }}">
                                {{ $typeLabel }}
                            </span>
                            @if (isset($data['student_name']))
                                <span class="rounded-md border border-line bg-card-2 px-2 py-0.5 text-xs font-semibold text-ink">
                                    Child: {{ $data['student_name'] }}
                                </span>
                            @endif
                            <time datetime="{{ $notification->created_at->toIso8601String() }}" class="text-xs text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </div>

                        <h2 class="mt-1.5 text-base font-semibold text-ink leading-snug">
                            {{ $data['subject'] ?? ($data['teacher_name'] ? "Note from {$data['teacher_name']}" : 'Academic Notification') }}
                        </h2>

                        @if (isset($data['preview']))
                            <p class="mt-1 text-sm text-muted line-clamp-2">
                                {{ $data['preview'] }}
                            </p>
                        @endif

                        @if (isset($data['teacher_name']))
                            <p class="mt-1 text-xs text-muted">
                                Sent by educator <span class="font-semibold text-ink">{{ $data['teacher_name'] }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 self-end sm:self-center">
                    @if (isset($data['action_url']))
                        <a href="{{ $data['action_url'] }}"
                            class="gg-button gg-button-primary px-3 py-1.5 text-xs font-semibold">
                            View Note
                        </a>
                    @endif

                    @if ($isUnread)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit"
                                class="gg-button gg-button-surface px-2.5 py-1.5 text-xs font-medium text-muted hover:text-ink"
                                title="Mark this alert as read">
                                Mark read
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="gg-brutal-card rounded-3xl p-10 text-center">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl border-2 border-line bg-card-2 text-muted">
                    @include('partials.icon', ['name' => 'bell', 'size' => 26])
                </div>
                <h3 class="mt-3 text-lg font-semibold text-ink">No notifications yet</h3>
                <p class="mt-1 text-sm text-muted">You will receive alerts here when educator notes, term reports, or academic milestones are available.</p>
            </div>
        @endforelse
    </section>

    {{-- Pagination --}}
    @if ($notifications->hasPages())
        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
