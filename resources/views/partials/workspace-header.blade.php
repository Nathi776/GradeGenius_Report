@php
    $user = auth()->user();
    $role = $user->role ?? 'user';
    $roleLabels = [
        'student' => 'Student',
        'parent' => 'Parent / Guardian',
        'teacher' => 'Educator',
        'administrator' => 'Administrator',
    ];
    $roleLabel = $roleLabels[$role] ?? ucfirst($role);
    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
@endphp

<header class="no-print mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-card px-4 py-3 sm:px-6">
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="relative flex size-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-good opacity-75"></span>
                <span class="relative inline-flex size-2.5 rounded-full bg-good"></span>
            </span>
            <span class="text-xs font-medium text-ink">Academic Year 2026</span>
            <span class="text-xs text-muted">·</span>
            <span class="rounded-full border border-line bg-card-2 px-2.5 py-0.5 text-[11px] font-semibold text-ink">Term 4 Reports</span>
        </div>
    </div>

    <div class="flex items-center gap-2.5 sm:gap-3">
        {{-- Print / Save PDF Button --}}
        <button type="button" onclick="window.print()"
            title="Print or Save Report as PDF"
            class="inline-flex items-center gap-2 rounded-xl border border-line bg-card-2 px-3 py-1.5 text-xs font-semibold text-ink transition hover:border-brand/50 hover:bg-card focus-visible:outline-2 focus-visible:outline-blue">
            @include('partials.icon', ['name' => 'printer', 'size' => 15])
            <span class="hidden sm:inline">Print / Save PDF</span>
            <span class="sm:hidden">Print</span>
        </button>

        {{-- Notification Indicator --}}
        <div class="relative group">
            <button type="button" aria-label="Notifications"
                class="relative inline-flex size-9 items-center justify-center rounded-xl border border-line bg-card-2 text-ink transition hover:border-brand/40 focus-visible:outline-2 focus-visible:outline-blue">
                @include('partials.icon', ['name' => 'bell', 'size' => 16])
                <span class="absolute -top-1 -right-1 flex size-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand opacity-75"></span>
                    <span class="relative inline-flex size-3 rounded-full bg-brand"></span>
                </span>
            </button>
            <div class="invisible group-hover:visible absolute right-0 top-full mt-2 w-72 rounded-xl border border-line bg-card p-3 shadow-xl z-50 text-xs">
                <p class="font-semibold text-ink border-b border-line pb-2 mb-2 flex items-center justify-between">
                    <span>Notifications</span>
                    <span class="text-[10px] text-muted font-normal">1 new</span>
                </p>
                <div class="rounded-lg bg-card-2 p-2">
                    <p class="font-medium text-ink">Term 4 Results Published</p>
                    <p class="mt-0.5 text-muted">All subject marks and diagnostic insights have been verified.</p>
                </div>
            </div>
        </div>

        {{-- User Profile Pill --}}
        <div class="flex items-center gap-2 rounded-xl border border-line bg-card-2 pl-2 pr-3 py-1">
            <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-brand to-sky text-xs font-bold text-white shadow-xs">
                {{ $initial }}
            </div>
            <div class="hidden text-left sm:block">
                <p class="truncate text-xs font-semibold text-ink leading-tight">{{ \Illuminate\Support\Str::before($user->name ?? 'User', ' ') }}</p>
                <p class="text-[10px] text-muted leading-none">{{ $roleLabel }}</p>
            </div>
        </div>
    </div>
</header>
