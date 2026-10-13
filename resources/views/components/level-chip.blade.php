@props(['level'])
@php
    // CAPS achievement level (1-7), the scale parents already know from school reports.
    $tone = $level >= 6 ? 'bg-good' : ($level >= 4 ? 'bg-warn' : 'bg-bad');
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-md px-1.5 py-0.5 text-xs font-semibold text-black', $tone]) }} title="Achievement level {{ $level }} of 7">
    <span aria-hidden="true">L{{ $level }}</span><span class="sr-only">Achievement level {{ $level }} of 7</span>
</span>
