@extends('layouts.parent', ['title' => 'Educator Note: '.$message->subject, 'active' => 'notifications'])

@section('parent-content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('parent.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('notifications.index') }}" class="hover:underline">Notifications</a>
        <span>/</span>
        <span class="text-ink">Note from Educator</span>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @php
        $typeLabels = [
            'progress_update' => 'Progress Update',
            'concern' => 'Academic Concern',
            'praise' => 'Commendation',
            'meeting_request' => 'Consultation Request',
        ];
        $typeLabel = $typeLabels[$message->type] ?? ucfirst($message->type);
        $tone = match($message->type) {
            'concern' => 'border-bad/40 bg-bad/10 text-bad',
            'praise' => 'border-good/40 bg-good/10 text-good',
            'meeting_request' => 'border-warn/40 bg-warn/10 text-warn',
            default => 'border-brand/40 bg-brand/10 text-ink',
        };
    @endphp

    <article class="gg-brutal-card rounded-3xl p-6 sm:p-8 bg-card border-2 border-black space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-line pb-5">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider {{ $tone }}">
                        {{ $typeLabel }}
                    </span>
                    <span class="rounded-md border border-line bg-card-2 px-2.5 py-0.5 text-xs font-semibold text-ink">
                        Regarding: {{ $message->student->name }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-ink tracking-tight pt-1">
                    {{ $message->subject }}
                </h1>
                <p class="text-xs text-muted">
                    Sent by educator <span class="font-semibold text-ink">{{ $message->teacher->name }}</span> · {{ $message->created_at->format('d M Y \a\t H:i') }}
                </p>
            </div>

            <div class="shrink-0 text-right">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-good/40 bg-good/15 px-3 py-1 text-xs font-semibold text-good">
                    <span>✓</span> Verified Note
                </span>
            </div>
        </div>

        {{-- Note Body --}}
        <div class="prose max-w-none text-ink text-sm sm:text-base leading-relaxed whitespace-pre-line bg-card-2 p-5 sm:p-6 rounded-2xl border border-line">
            {{ $message->body }}
        </div>

        {{-- Quick Acknowledgement / Response Section --}}
        <div class="rounded-2xl border-2 border-line bg-cream p-5 sm:p-6 space-y-4">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-ink">Acknowledge this note</h2>
                <p class="text-xs text-muted mt-0.5">Let {{ $message->teacher->name }} know you received and reviewed this communication.</p>
            </div>

            @if ($message->isAcknowledged())
                <div class="flex items-center gap-2 rounded-xl border border-good/40 bg-good/20 p-3.5 text-xs font-semibold text-ink">
                    <span class="text-good font-bold">✓</span>
                    <span>You acknowledged this note on {{ $message->acknowledged_at->format('d M Y, H:i') }} with response: <strong>"{{ $message->quick_reply }}"</strong>.</span>
                </div>
            @else
                <form method="POST" action="{{ route('parent.messages.acknowledge', $message->id) }}" class="space-y-4">
                    @csrf
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" name="quick_reply" value="Thanks, noted"
                            class="gg-button gg-button-primary px-3.5 py-2 text-xs font-semibold">
                            Thanks, noted
                        </button>
                        <button type="submit" name="quick_reply" value="Please call me"
                            class="gg-button gg-button-surface px-3.5 py-2 text-xs font-semibold text-ink hover:border-black">
                            Please call me
                        </button>
                        <button type="submit" name="quick_reply" value="Understood, following up at home"
                            class="gg-button gg-button-surface px-3.5 py-2 text-xs font-semibold text-ink hover:border-black">
                            Understood, following up at home
                        </button>
                        <button type="submit" name="quick_reply" value="Request meeting time"
                            class="gg-button gg-button-surface px-3.5 py-2 text-xs font-semibold text-ink hover:border-black">
                            Request meeting time
                        </button>
                    </div>

                    <div class="border-t border-line/60 pt-3">
                        <label for="custom_reply" class="block text-xs font-semibold text-ink mb-1">
                            Or send a brief custom response (optional):
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="custom_reply" id="custom_reply" maxlength="255"
                                placeholder="e.g. We will revise the geometry questions this evening, thank you."
                                class="flex-1 rounded-xl border-2 border-line bg-card px-3 py-2 text-xs font-medium text-ink focus:outline-none focus:border-brand">
                            <button type="submit" class="gg-button gg-button-primary px-4 py-2 text-xs font-semibold shrink-0">
                                Send Reply
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <div class="pt-2 flex items-center justify-between">
            <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-muted hover:text-ink underline underline-offset-2">
                ← Back to All Notifications
            </a>
            <a href="{{ route('parent.home') }}" class="gg-button gg-button-surface px-3.5 py-2 text-xs font-semibold">
                Go to Parent Dashboard
            </a>
        </div>
    </article>
</div>
@endsection
