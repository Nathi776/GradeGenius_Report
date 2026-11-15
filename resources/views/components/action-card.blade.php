@props(['title', 'subject', 'mastery', 'priority' => 'Medium', 'tone' => 'mid', 'href', 'actionText' => 'Revise now', 'color' => null])
@php
    $badge = [
        'low' => 'border-bad/30 bg-bad/15 text-bad',
        'mid' => 'border-warn/30 bg-warn/15 text-warn',
        'good' => 'border-good/30 bg-good/15 text-good',
    ][$tone] ?? 'border-line bg-card-2 text-muted';
@endphp
<article {{ $attributes->class(['group relative overflow-hidden rounded-xl border border-line bg-card-2 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-brand/40']) }}>
    <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-brand to-blue" aria-hidden="true"></div>
    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3">
        <div class="min-w-48 flex-1">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badge }}">{{ $priority }} priority</span>
                <span class="flex items-center gap-1.5 text-xs font-semibold text-muted">
                    @if ($color)<span class="size-2 shrink-0 rounded-full" style="background: {{ $color }}" aria-hidden="true"></span>@endif
                    {{ $subject }}
                </span>
            </div>
            <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
            <p class="mt-1 text-sm text-muted">Topic mastery: <span class="font-semibold tabular-nums text-white">{{ $mastery }}%</span></p>
        </div>
        <a href="{{ $href }}" class="gg-button gg-button-primary shrink-0 whitespace-nowrap text-xs">{{ $actionText }}<span class="sr-only"> for {{ $title }} in {{ $subject }}</span> →</a>
    </div>
</article>
