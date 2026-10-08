@props(['label', 'value', 'variant' => 'topic', 'tone' => 'good'])
@php
    // Topics use the brand blue, but weak topics turn amber / red so the bar itself says "look here".
    $fill = $variant === 'paper'
        ? 'bg-linear-to-r from-brand to-warn'
        : (['good' => 'bg-blue', 'mid' => 'bg-warn', 'low' => 'bg-bad'][$tone] ?? 'bg-blue');
@endphp
<div class="grid grid-cols-[minmax(6.5rem,11rem)_1fr_3rem] items-center gap-3 text-sm">
    <span class="truncate text-slate-100" title="{{ $label }}">{{ $label }}</span>
    <div class="h-2 overflow-hidden rounded-full bg-line" role="presentation">
        <div class="h-full rounded-full {{ $fill }}" style="width: {{ $value }}%"></div>
    </div>
    <span class="text-right font-semibold tabular-nums text-white">{{ $value }}%</span>
</div>
