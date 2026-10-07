@php($paths = [
    'arrow-right' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
    'bar-chart' => '<path d="M4 19V5m0 14h16M8 16v-5m4 5V7m4 9v-8"/>',
    'book-open' => '<path d="M3 5.5A2.5 2.5 0 0 1 5.5 3H12v17H5.5A2.5 2.5 0 0 0 3 22V5.5Zm18 0A2.5 2.5 0 0 0 18.5 3H12v17h6.5A2.5 2.5 0 0 1 21 22V5.5Z"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'graduation-cap' => '<path d="m2 10 10-5 10 5-10 5L2 10Z"/><path d="M6 12v5c3 2 9 2 12 0v-5M22 10v6"/>',
    'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
    'trending-up' => '<path d="m3 17 6-6 4 4 7-8"/><path d="M15 7h5v5"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7a4 4 0 0 1 0 7.8M22 21v-2a4 4 0 0 0-3-3.9"/>',
])
<svg class="gg-icon" width="{{ $size ?? 20 }}" height="{{ $size ?? 20 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] !!}</svg>
