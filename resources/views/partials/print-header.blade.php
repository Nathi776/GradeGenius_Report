@props([
    'title' => 'Official Academic Performance Report',
    'student' => null,
    'term' => 'Term 4',
    'academicYear' => '2026',
])

<div class="print-only mb-6 border-b-2 border-slate-900 pb-4 text-slate-900">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">GradeGenius Academic Intelligence</h1>
            <p class="text-sm font-semibold uppercase tracking-wider text-slate-600">{{ $title }} · {{ $academicYear }}</p>
            <p class="text-xs text-slate-500">South African Curriculum Assessment Policy Statements (CAPS) Aligned</p>
        </div>
        <div class="text-right text-xs text-slate-600">
            <p><span class="font-semibold text-slate-900">Academic Term:</span> {{ $term }}</p>
            <p><span class="font-semibold text-slate-900">Issue Date:</span> {{ now()->format('d M Y') }}</p>
            <p><span class="font-semibold text-slate-900">Report Status:</span> Official Verified</p>
        </div>
    </div>

    @if ($student)
        <div class="mt-4 grid grid-cols-3 gap-2 rounded-lg border border-slate-300 bg-slate-50 p-3 text-xs">
            <div>
                <span class="text-slate-500">Learner Name:</span>
                <p class="font-semibold text-slate-900">{{ \Illuminate\Support\Str::before($student['name'] ?? $student, ' ') }}</p>
            </div>
            <div>
                <span class="text-slate-500">Grade / Class:</span>
                <p class="font-semibold text-slate-900">Grade {{ $student['grade'] ?? '12' }}</p>
            </div>
            <div>
                <span class="text-slate-500">National Target:</span>
                <p class="font-semibold text-slate-900">Bachelor Degree Pass (Level 5+)</p>
            </div>
        </div>
    @endif
</div>
