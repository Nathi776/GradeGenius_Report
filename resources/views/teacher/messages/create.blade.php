@extends('layouts.teacher', ['title' => 'Message Parent · '.$student->name, 'active' => 'messages'])

@section('teacher-content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('teacher.messages.index') }}" class="hover:underline">Sent Notes</a>
        <span>/</span>
        <span class="text-ink">Message Parent</span>
    </div>

    @if (session('error'))
        <div class="rounded-2xl border border-bad/40 bg-bad/15 p-4 text-sm font-semibold text-ink" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Learner Context Banner --}}
    <header class="gg-brutal-card rounded-3xl p-5 sm:p-6 bg-cream border-2 border-black flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="size-14 rounded-2xl bg-sky border-2 border-black flex items-center justify-center font-display font-bold text-2xl text-ink">
                {{ strtoupper(substr($student->name, 0, 1)) }}
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-muted">Learner Profile</span>
                <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $student->name }}</h1>
                <p class="text-xs text-muted mt-0.5">{{ $subject }} · Grade 12 · Target {{ $target }}%</p>
            </div>
        </div>

        @if ($mark !== null)
            <div class="flex items-center gap-3 rounded-2xl border-2 border-black bg-white px-4 py-2.5">
                <div>
                    <p class="text-[11px] font-bold uppercase text-muted">Current Mark</p>
                    <p class="text-xl font-bold {{ $mark < $target ? 'text-bad' : 'text-good' }}">{{ $mark }}%</p>
                </div>
                <div class="h-8 w-px bg-line"></div>
                <div>
                    <p class="text-[11px] font-bold uppercase text-muted">Target</p>
                    <p class="text-xl font-bold text-ink">{{ $target }}%</p>
                </div>
            </div>
        @endif
    </header>

    {{-- Message Composition Form --}}
    <div class="gg-brutal-card rounded-3xl p-5 sm:p-8 bg-card border-2 border-black space-y-6">
        <div>
            <h2 class="text-xl font-bold text-ink">Compose Educator Note</h2>
            <p class="mt-1 text-sm text-muted">Send a personalized one-way update to the confirmed guardian. Choose a template or write your own note.</p>
        </div>

        <form method="POST" action="{{ route('teacher.messages.store') }}" class="space-y-6" id="teacherMessageForm">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">

            {{-- Verified Parent Recipient --}}
            <div>
                <label for="parent_id" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">
                    Verified Guardian Recipient <span class="text-bad">*</span>
                </label>
                <select name="parent_id" id="parent_id" required
                    class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm font-semibold text-ink focus:outline-none focus:border-brand">
                    @foreach ($verifiedParents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->email }}) — Confirmed Guardian
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                @enderror
            </div>

            {{-- Template Chooser --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-ink mb-2">
                    Note Purpose & Template
                </label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <button type="button" onclick="selectTemplate('concern')" data-type-btn="concern"
                        class="template-btn rounded-xl border-2 border-black px-3 py-2.5 text-xs font-bold transition text-left cursor-pointer bg-bad/15 border-bad text-ink">
                        <span class="block font-bold">Academic Concern</span>
                        <span class="text-[10px] text-muted">Below goal mark</span>
                    </button>
                    <button type="button" onclick="selectTemplate('progress_update')" data-type-btn="progress_update"
                        class="template-btn rounded-xl border-2 border-line bg-card-2 px-3 py-2.5 text-xs font-bold transition text-left cursor-pointer hover:border-black text-ink">
                        <span class="block font-bold">Progress Update</span>
                        <span class="text-[10px] text-muted">Routine review</span>
                    </button>
                    <button type="button" onclick="selectTemplate('praise')" data-type-btn="praise"
                        class="template-btn rounded-xl border-2 border-line bg-card-2 px-3 py-2.5 text-xs font-bold transition text-left cursor-pointer hover:border-black text-ink">
                        <span class="block font-bold">Commendation</span>
                        <span class="text-[10px] text-muted">Praise effort</span>
                    </button>
                    <button type="button" onclick="selectTemplate('meeting_request')" data-type-btn="meeting_request"
                        class="template-btn rounded-xl border-2 border-line bg-card-2 px-3 py-2.5 text-xs font-bold transition text-left cursor-pointer hover:border-black text-ink">
                        <span class="block font-bold">Consultation</span>
                        <span class="text-[10px] text-muted">Request meeting</span>
                    </button>
                </div>
                <input type="hidden" name="type" id="messageTypeInput" value="{{ old('type', 'concern') }}">
            </div>

            {{-- Subject Line --}}
            <div>
                <label for="subject" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">
                    Subject Line <span class="text-bad">*</span>
                </label>
                <input type="text" name="subject" id="subjectInput" required
                    value="{{ old('subject', $templates['concern']['subject']) }}"
                    class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm font-semibold text-ink focus:outline-none focus:border-brand">
                @error('subject')
                    <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message Body --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="body" class="block text-xs font-bold uppercase tracking-wider text-ink">
                        Message Content <span class="text-bad">*</span>
                    </label>
                    <span class="text-[11px] text-muted">Editable draft</span>
                </div>
                <textarea name="body" id="bodyInput" rows="7" required
                    class="w-full rounded-xl border-2 border-black bg-card-2 p-3.5 text-sm font-medium text-ink focus:outline-none focus:border-brand leading-relaxed">{{ old('body', $templates['concern']['body']) }}</textarea>
                @error('body')
                    <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                @enderror
            </div>

            {{-- Policy & Safety Notice --}}
            <div class="rounded-xl border border-line bg-card-2 p-3 text-xs text-muted flex items-start gap-2.5">
                <span class="text-base leading-none">ℹ️</span>
                <p>This message will be dispatched directly to the confirmed guardian's notifications center. Messages concern minors' academic progress; ensure all remarks adhere to official school communication guidelines. Nothing is sent automatically.</p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <a href="{{ route('teacher.home') }}" class="gg-button gg-button-surface px-4 py-2.5 text-xs font-semibold text-muted hover:text-ink">
                    Cancel & Return
                </a>
                <button type="submit" class="gg-button gg-button-primary px-6 py-2.5 text-xs font-bold">
                    Send Note to Guardian
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const templates = @json($templates);

    function selectTemplate(type) {
        document.getElementById('messageTypeInput').value = type;

        // Update button states
        document.querySelectorAll('.template-btn').forEach(btn => {
            if (btn.dataset.typeBtn === type) {
                btn.className = 'template-btn rounded-xl border-2 border-black px-3 py-2.5 text-xs font-bold transition text-left cursor-pointer bg-butter shadow-xs text-ink';
            } else {
                btn.className = 'template-btn rounded-xl border-2 border-line bg-card-2 px-3 py-2.5 text-xs font-bold transition text-left cursor-pointer hover:border-black text-ink';
            }
        });

        // Set inputs if template exists
        if (templates[type]) {
            document.getElementById('subjectInput').value = templates[type].subject;
            document.getElementById('bodyInput').value = templates[type].body;
        }
    }
</script>
@endsection
