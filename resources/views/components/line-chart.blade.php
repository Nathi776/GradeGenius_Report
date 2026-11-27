{{--
    Multi-series line chart drawn as inline SVG.
    - every series is drawn through its own points (no separate "trend" line that disagrees with the dots)
    - y-axis ticks are evenly spaced AND evenly valued
    series: [['key','name','color','points'=>[...],'area'=>bool,'labels'=>bool], ...]
--}}
@props(['series', 'labels', 'id' => 'chart', 'height' => 300])
@php
    $w = 760; $h = $height; $pl = 46; $pr = 24; $pt = 22; $pb = 38;

    $values = [];
    foreach ($series as $s) {
        foreach ($s['points'] as $v) {
            if ($v !== null) { $values[] = $v; }
        }
    }
    $recordedTerms = [];
    foreach ($series as $s) {
        foreach (array_values($s['points']) as $i => $v) {
            if ($v !== null) { $recordedTerms[$i] = true; }
        }
    }
    $hasTrend = count($recordedTerms) >= 2;
    $lo = $values === [] ? 0 : max(0, (int) (floor((min($values) - 8) / 20) * 20));
    $hi = 100;
    $n = count($labels);

    $x = fn ($i) => $n === 1 ? $w / 2 : $pl + $i * (($w - $pl - $pr) / ($n - 1));
    $y = fn ($v) => $pt + (($hi - $v) / ($hi - $lo)) * ($h - $pt - $pb);
    $ticks = range($lo, $hi, 20);

    $summary = [];
    foreach ($series as $s) {
        if ($s['key'] === 'overall') {
            $summary[] = 'Overall average by term: ' . implode(', ', array_filter(array_map(fn ($l, $v) => $v === null ? null : "$l $v percent", $labels, array_values($s['points']))));
        }
    }
@endphp
@if (! $hasTrend)
    <div class="flex items-center justify-center rounded-xl border border-dashed border-line px-4 text-center text-sm text-muted" style="min-height: {{ min($height, 140) }}px">
        Marks from at least two terms are needed to show a trend. They will appear here as terms are recorded.
    </div>
@else
<svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-auto w-full" role="img" aria-label="{{ implode('. ', $summary) ?: 'Progress over time' }}">
    <defs>
        <linearGradient id="{{ $id }}-area" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" style="stop-color: var(--chart-area, #f23d99)" stop-opacity="0.35" />
            <stop offset="100%" style="stop-color: var(--chart-area, #f23d99)" stop-opacity="0" />
        </linearGradient>
    </defs>

    @foreach ($ticks as $t)
        <line x1="{{ $pl }}" x2="{{ $w - $pr }}" y1="{{ round($y($t), 1) }}" y2="{{ round($y($t), 1) }}" class="stroke-line" stroke-dasharray="3 5" />
        <text x="{{ $pl - 10 }}" y="{{ round($y($t), 1) + 4 }}" text-anchor="end" class="fill-current text-muted" font-size="12">{{ $t }}</text>
    @endforeach

    @foreach ($labels as $i => $label)
        <text x="{{ round($x($i), 1) }}" y="{{ $h - 10 }}" text-anchor="middle" class="fill-current text-muted" font-size="12">{{ $label }}</text>
    @endforeach

    {{-- subject lines first, overall last so it sits on top --}}
    @foreach (array_merge(array_slice($series, 1), array_slice($series, 0, 1)) as $s)
        @php
            $isOverall = $s['key'] === 'overall';
            $segments = [];
            $recordedIndexes = [];
            foreach (array_values($s['points']) as $i => $v) {
                if ($v === null) {
                    continue;
                }
                $segments[] = ($segments === [] ? 'M' : 'L') . round($x($i), 1) . ',' . round($y($v), 1);
                $recordedIndexes[] = $i;
            }
            if ($recordedIndexes === []) {
                continue;
            }
            $line = implode(' ', $segments);
            $firstIndex = $recordedIndexes[0];
            $lastIndex = end($recordedIndexes);
        @endphp
        <g data-series="{{ $s['key'] }}">
            @if ($s['area'])
                <path d="{{ $line }} L{{ round($x($lastIndex), 1) }},{{ round($y($lo), 1) }} L{{ round($x($firstIndex), 1) }},{{ round($y($lo), 1) }} Z" fill="url(#{{ $id }}-area)" />
            @endif
            <path d="{{ $line }}" fill="none" stroke="{{ $s['color'] }}" @if ($isOverall) class="text-ink" @endif stroke-width="{{ $isOverall ? 3 : 2 }}" stroke-linecap="round" stroke-linejoin="round" @if (! $isOverall) stroke-opacity="0.85" @endif />
            @foreach ($s['points'] as $i => $v)
                @continue($v === null)
                <circle cx="{{ round($x($i), 1) }}" cy="{{ round($y($v), 1) }}" r="{{ $isOverall ? 4.5 : 3 }}" @if ($isOverall) style="fill: var(--chart-area, #f23d99)" @else fill="{{ $s['color'] }}" @endif>
                    <title>{{ $s['name'] }}, {{ $labels[$i] }}: {{ $v }}%</title>
                </circle>
                @if ($s['labels'] ?? false)
                    <text x="{{ round($x($i), 1) }}" y="{{ round($y($v), 1) - 12 }}" text-anchor="middle" font-size="12" font-weight="600" class="fill-current text-ink">{{ $v }}%</text>
                @endif
            @endforeach
        </g>
    @endforeach
</svg>
@endif
