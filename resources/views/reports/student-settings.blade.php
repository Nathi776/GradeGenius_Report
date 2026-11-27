@extends('layouts.student', ['title' => 'Settings', 'active' => 'settings'])

@php
    $card = 'gg-brutal-card rounded-3xl p-5 sm:p-6';
    $button = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
@endphp

@section('student-content')
    <div class="space-y-5">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight text-ink">Settings</h1>
            <p class="mt-1 text-sm text-muted">Control who can see your activity and which parent accounts are connected to you.</p>
        </header>

        @if (session('status'))
            <p role="status" class="rounded-xl border border-line bg-good/10 p-3 text-sm font-medium text-ink">{{ session('status') }}</p>
        @endif

        <section aria-labelledby="privacy-heading" class="{{ $card }}">
            <h2 id="privacy-heading" class="text-lg font-normal text-ink">Privacy: activity sharing</h2>
            <p class="mt-1 text-sm text-muted">GradeGenius records when you log in and which pages you visit. This is private by default. If you turn sharing on, your confirmed parents and your teachers can see when you were online. Your marks are shown to your parents and teachers either way.</p>
            <p class="mt-3 text-sm text-ink">Currently: <span class="font-semibold">{{ $sharesActivity ? 'Shared with parents and teachers' : 'Private, only you can see it' }}</span></p>
            <form method="POST" action="{{ route('student.privacy.activity-sharing') }}" class="mt-3">
                @csrf
                <input type="hidden" name="shares_activity" value="{{ $sharesActivity ? 0 : 1 }}">
                <button type="submit" class="{{ $button }}">{{ $sharesActivity ? 'Make my activity private' : 'Share my activity with parents and teachers' }}</button>
            </form>
        </section>

        <section aria-labelledby="guardian-link-heading" class="{{ $card }}">
            <h2 id="guardian-link-heading" class="text-lg font-normal text-ink">Parent account connection</h2>
            <p class="mt-1 text-sm text-muted">Link a verified parent account so they can see your reports. The parent must confirm the link before they can see anything.</p>
            @if ($guardians !== [])
                <ul class="mt-3 space-y-2">
                    @foreach ($guardians as $guardian)
                        <li class="rounded-xl border border-line bg-card-2 p-3 text-sm text-ink">{{ $guardian['name'] }} · {{ $guardian['email'] }} <span class="ml-1 text-xs text-muted">({{ $guardian['verified'] ? 'confirmed' : 'waiting for parent to confirm' }})</span></li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('student.guardians.store') }}" class="mt-3 flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-60 flex-1">
                    <label for="parent_email" class="mb-1 block text-sm font-medium text-ink">Verified parent email</label>
                    <input id="parent_email" name="parent_email" type="email" value="{{ old('parent_email') }}" required autocomplete="email"
                        class="w-full rounded-xl border border-line bg-card-2 px-3 py-2 text-sm text-ink focus-visible:outline-2 focus-visible:outline-blue">
                    @error('parent_email')
                        <p class="mt-1 text-xs text-bad">{{ $message }}</p>
                    @enderror
                </div>
                <button class="{{ $button }}" type="submit">Link parent account</button>
            </form>
            <p class="mt-2 text-xs text-muted">The email must belong to an existing verified parent account.</p>
        </section>
    </div>
@endsection
