@props(['label', 'value', 'sub' => null, 'tone' => 'default', 'compact' => false])
@php
    $valueClass = [
        'default' => 'text-white', 'good' => 'text-good', 'mid' => 'text-warn',
        'low' => 'text-bad', 'sky' => 'text-sky', 'peach' => 'text-warn',
    ][$tone] ?? 'text-white';
@endphp
<div {{ $attributes->class(['rounded-xl border border-line bg-card-2', $compact ? 'p-3' : 'p-4']) }}>
    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold leading-none tabular-nums {{ $valueClass }}">{{ $value }}</p>
    @if ($sub)
        <p class="mt-2 text-xs text-muted">{{ $sub }}</p>
    @endif
</div>
