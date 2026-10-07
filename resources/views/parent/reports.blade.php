@extends('layouts.app')
@section('title', 'Parent Report | GradeGenius')
@section('content')
<header class="gg-header"><a href="{{ route('dashboard') }}" class="gg-brand"><span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap'])</span>GradeGenius</a><a href="{{ route('dashboard') }}" class="gg-button gg-button-surface">Back home</a></header>
<main class="gg-report">
    <h1>Your Children's Reports</h1><p class="gg-report-intro">Monitor marks and progress for each child.</p>
    <div class="gg-tabs">@foreach ($children as $name => $child)<button class="gg-tab {{ $loop->first ? 'active' : '' }}" type="button" data-child-tab="{{ $loop->index }}">@include('partials.icon', ['name' => 'users', 'size' => 16]) {{ $name }}</button>@endforeach</div>
    @foreach ($children as $name => $child)
        <section class="child-report" data-child-panel="{{ $loop->index }}" @if (!$loop->first) hidden @endif>
            <h2>{{ $name }} <small class="gg-card-sub">{{ $child['grade'] }}</small></h2>
            <div class="gg-metric-grid">
                <div class="gg-card"><div class="gg-card-label">Current Average</div><div class="gg-card-value">{{ $child['progress'][3] }}%</div><div class="gg-card-sub">Term 4</div></div>
                <div class="gg-card"><div class="gg-card-label">Improvement</div><div class="gg-card-value gg-value-green">+{{ $child['progress'][3] - $child['progress'][0] }}%</div><div class="gg-card-sub">Since Term 1</div></div>
                <div class="gg-card"><div class="gg-card-label">Subjects</div><div class="gg-card-value gg-value-orange">{{ count($child['subjects']) }}</div></div>
                <div class="gg-card"><div class="gg-card-label">Status</div><div class="gg-card-value gg-value-blue">On track</div><div class="gg-card-sub">10%+ goal met</div></div>
            </div>
            <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<h2>Progress Over Time</h2></div><div class="gg-bars">@foreach ($child['progress'] as $index => $mark)<div class="gg-bar-row"><span>Term {{ $index + 1 }}</span><div class="gg-bar"><span style="width: {{ $mark }}%"></span></div><strong>{{ $mark }}%</strong></div>@endforeach</div></div>
            <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'book-open', 'size' => 20])<h2>Subject Breakdown</h2></div><div class="gg-bars">@foreach ($child['subjects'] as $subject)<div class="gg-bar-row"><span>{{ $subject['name'] }}<small class="gg-card-sub">Grade {{ $subject['grade'] }}</small></span><div class="gg-bar"><span style="width: {{ $subject['mark'] }}%"></span></div><strong>{{ $subject['mark'] }}%</strong></div>@endforeach</div></div>
        </section>
    @endforeach
</main>
<script>document.querySelectorAll('[data-child-tab]').forEach(function (tab) { tab.addEventListener('click', function () { document.querySelectorAll('[data-child-tab]').forEach(function (item) { item.classList.toggle('active', item === tab); }); document.querySelectorAll('[data-child-panel]').forEach(function (panel) { panel.hidden = panel.dataset.childPanel !== tab.dataset.childTab; }); }); });</script>
@endsection
