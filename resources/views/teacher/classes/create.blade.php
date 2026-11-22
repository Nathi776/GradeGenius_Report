@extends('layouts.teacher', ['title' => 'New Class', 'active' => 'classes'])

@section('teacher-content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted">
        <a href="{{ route('teacher.home') }}" class="hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('teacher.classes.index') }}" class="hover:underline">My Classes</a>
        <span>/</span>
        <span class="text-ink">New</span>
    </div>

    <div class="rounded-3xl border-2 border-black bg-card p-5 sm:p-8 space-y-6">
        <h1 class="text-xl font-bold text-ink">Create a class</h1>

        <form method="POST" action="{{ route('teacher.classes.store') }}" class="space-y-5">
            @csrf
            @foreach ([['name', 'Class name', 'text', 'e.g. Grade 12 Maths A'], ['subject', 'Subject', 'text', 'e.g. Mathematics']] as [$field, $label, $type, $placeholder])
                <div>
                    <label for="{{ $field }}" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">{{ $label }}</label>
                    <input type="{{ $type }}" name="{{ $field }}" id="{{ $field }}" required maxlength="120" placeholder="{{ $placeholder }}" value="{{ old($field) }}" class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink">
                    @error($field)
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="grade" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">Grade</label>
                    <input type="number" name="grade" id="grade" required min="1" max="12" value="{{ old('grade', 12) }}" class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink">
                    @error('grade')
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="target" class="block text-xs font-bold uppercase tracking-wider text-ink mb-1.5">Target mark (%)</label>
                    <input type="number" name="target" id="target" min="0" max="100" value="{{ old('target', 65) }}" class="w-full rounded-xl border-2 border-black bg-card-2 px-3.5 py-2.5 text-sm text-ink">
                    @error('target')
                        <p class="mt-1 text-xs font-medium text-bad">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="gg-button gg-button-primary px-5 py-2.5 text-sm font-bold">Create class</button>
        </form>
    </div>
</div>
@endsection
