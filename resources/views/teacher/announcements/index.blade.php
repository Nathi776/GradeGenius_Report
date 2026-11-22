@extends('layouts.teacher', ['title' => 'Class Announcements', 'active' => 'announcements'])

@section('teacher-content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <span class="text-ink">Announcements</span>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-good/40 bg-good/15 p-4 text-sm font-semibold text-ink" role="alert">{{ session('status') }}</div>
    @endif

    <header class="rounded-3xl border-2 border-black bg-card p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Class announcements</h1>
            <p class="mt-1 text-sm text-muted">A record of everything you have published to guardians.</p>
        </div>
        <a href="{{ route('teacher.announcements.create') }}" class="gg-button gg-button-primary px-4 py-2 text-xs font-bold">New announcement</a>
    </header>

    <div class="space-y-3">
        @forelse ($announcements as $announcement)
            <article class="rounded-2xl border border-line bg-card p-4 sm:p-5">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full border border-line bg-card-2 px-2 py-0.5 font-bold uppercase tracking-wider text-ink">{{ ucfirst($announcement->priority) }}</span>
                    <span class="text-muted">{{ $announcement->schoolClass->name }} · {{ $announcement->schoolClass->subject }}</span>
                    <span class="text-muted">· {{ $announcement->created_at->format('d M Y H:i') }}</span>
                    <span class="text-muted">· {{ $announcement->recipients_count }} guardian(s)</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold text-ink">{{ $announcement->title }}</h2>
                <p class="mt-1 text-sm text-muted whitespace-pre-line">{{ \Illuminate\Support\Str::limit($announcement->body, 200) }}</p>
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-line p-6 text-sm text-muted">No announcements yet.</p>
        @endforelse
    </div>

    {{ $announcements->links() }}
</div>
@endsection
