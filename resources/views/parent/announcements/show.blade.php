@extends('layouts.parent', ['title' => 'Class Announcement: '.$announcement->title, 'active' => 'notifications'])

@section('parent-content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('parent.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('notifications.index') }}" class="hover:underline">Notifications</a>
        <span>/</span>
        <span class="text-ink">Class announcement</span>
    </div>

    <article class="rounded-3xl border-2 border-black bg-card p-6 sm:p-8 space-y-5">
        <div class="border-b border-line pb-5 space-y-1">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="rounded-full border border-line bg-card-2 px-2.5 py-0.5 font-bold uppercase tracking-wider text-ink">{{ ucfirst($announcement->priority) }}</span>
                <span class="text-muted">{{ $announcement->schoolClass->name }} · {{ $announcement->schoolClass->subject }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-ink tracking-tight pt-1">{{ $announcement->title }}</h1>
            <p class="text-xs text-muted">From <span class="font-semibold text-ink">{{ $announcement->teacher->name }}</span> · {{ $announcement->created_at->format('d M Y \a\t H:i') }}</p>
        </div>
        <div class="text-sm leading-relaxed text-ink whitespace-pre-line">{{ $announcement->body }}</div>
    </article>
</div>
@endsection
