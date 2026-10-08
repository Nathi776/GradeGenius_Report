@props(['points', 'color' => '#5b7cfa', 'width' => 88, 'height' => 28])
@php
    $n = count($points);
    $lo = min($points) - 3;
    $hi = max($points) + 3;
    $px = fn ($i) => $n > 1 ? 3 + $i * (($width - 6) / ($n - 1)) : $width / 2;
    $py = fn ($v) => 3 + (($hi - $v) / max($hi - $lo, 1)) * ($height - 6);
    $segments = [];
    foreach (array_values($points) as $i => $v) {
        $segments[] = ($i === 0 ? 'M' : 'L') . round($px($i), 1) . ',' . round($py($v), 1);
    }
    $last = $n - 1;
@endphp
<svg {{ $attributes }} width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}" aria-hidden="true">
    <path d="{{ implode(' ', $segments) }}" fill="none" stroke="{{ $color }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    <circle cx="{{ round($px($last), 1) }}" cy="{{ round($py($points[$last]), 1) }}" r="2.5" fill="{{ $color }}" />
</svg>
