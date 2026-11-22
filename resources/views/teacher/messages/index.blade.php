@extends('layouts.teacher', ['title' => 'Sent Educator Notes', 'active' => 'messages'])

@section('teacher-content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <span class="text-ink">Sent Notes</span>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <header class="gg-brutal-card rounded-3xl p-5 sm:p-6 bg-card border-2 border-black flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Educator Sent Notes</h1>
            <p class="mt-1 text-sm text-muted">Record of all academic notices, progress updates, and meeting requests sent to confirmed guardians.</p>
        </div>
        <a href="{{ route('teacher.home') }}" class="gg-button gg-button-primary px-4 py-2 text-xs font-bold">
            Back to Dashboard
        </a>
    </header>

    <div class="space-y-3">
        @forelse ($messages as $msg)
            @php
                $typeLabels = [
                    'progress_update' => 'Progress Update',
                    'concern' => 'Academic Concern',
                    'praise' => 'Commendation',
                    'meeting_request' => 'Consultation Request',
                ];
                $typeLabel = $typeLabels[$msg->type] ?? ucfirst($msg->type);
                $tone = match($msg->type) {
                    'concern' => 'border-bad/40 bg-bad/10 text-bad',
                    'praise' => 'border-good/40 bg-good/10 text-good',
                    'meeting_request' => 'border-warn/40 bg-warn/10 text-warn',
                    default => 'border-brand/40 bg-brand/10 text-ink',
                };
            @endphp

            <article class="gg-brutal-card rounded-2xl border-2 border-black bg-card p-5 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider {{ $tone }}">
                            {{ $typeLabel }}
                        </span>
                        <span class="rounded-md border border-line bg-card-2 px-2.5 py-0.5 text-xs font-semibold text-ink">
                            Learner: {{ $msg->student->name }}
                        </span>
                        <span class="rounded-md border border-line bg-card-2 px-2.5 py-0.5 text-xs text-muted">
                            To: {{ $msg->parent->name }} ({{ $msg->parent->email }})
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <time datetime="{{ $msg->created_at->toIso8601String() }}" class="text-muted">
                            Sent {{ $msg->created_at->format('d M Y, H:i') }}
                        </time>

                        {{-- Read Status Indicator --}}
                        @if ($msg->isRead())
                            <span class="inline-flex items-center gap-1 rounded-full border border-good/40 bg-good/15 px-2.5 py-0.5 font-semibold text-good">
                                <span>✓</span> Read {{ $msg->read_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full border border-line bg-card-2 px-2.5 py-0.5 font-medium text-muted">
                                Unread by guardian
                            </span>
                        @endif

                        {{-- Acknowledged Status Indicator --}}
                        @if ($msg->isAcknowledged())
                            <span class="inline-flex items-center gap-1 rounded-full border border-blue/40 bg-blue/15 px-2.5 py-0.5 font-semibold text-ink">
                                Reply: "{{ $msg->quick_reply }}"
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="text-base font-bold text-ink">{{ $msg->subject }}</h2>
                    <p class="mt-2 text-sm text-ink whitespace-pre-line leading-relaxed font-normal bg-card-2 p-3.5 rounded-xl border border-line">
                        {{ $msg->body }}
                    </p>
                </div>
            </article>
        @empty
            <div class="gg-brutal-card rounded-3xl p-10 text-center border-2 border-line bg-card">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl border-2 border-line bg-card-2 text-muted">
                    @include('partials.icon', ['name' => 'mail', 'size' => 22])
                </div>
                <h3 class="mt-3 text-base font-semibold text-ink">No notes sent yet</h3>
                <p class="mt-1 text-sm text-muted">When you send academic updates or consultation requests to guardians, they will appear here along with delivery and read receipts.</p>
            </div>
        @endforelse
    </div>

    @if ($messages->hasPages())
        <div class="pt-4">
            {{ $messages->links() }}
        </div>
    @endif
</div>
@endsection
