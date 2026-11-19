@extends('layouts.app')
@section('title', 'Teacher Report | GradeGenius')
@section('content')
@include('partials.report-header')
@php
    $termChange = $selectedTermIndex > 0 ? $progress[$selectedTermIndex] - $progress[$selectedTermIndex - 1] : 0;
@endphp
<main class="gg-report">
    @include('partials.print-header', [
        'title' => 'Educator Class Marksheet & Performance Audit',
        'term' => $selectedTerm === 'all' ? 'All Terms' : $terms[$selectedTermIndex],
    ])
    <div class="no-print flex flex-wrap items-center justify-between gap-3 mb-4">
        <a href="{{ route('teacher.home') }}" class="gg-nav-link">← Back to teacher dashboard</a>
        <button type="button" onclick="window.print()" class="gg-button gg-button-surface">
            @include('partials.icon', ['name' => 'printer', 'size' => 15])
            <span>Print Class Marksheet</span>
        </button>
    </div>
    <h1>Class Performance Report</h1><p class="gg-report-intro">Grade 11 Physical Sciences — 2026 Academic Year · {{ $terms[$selectedTermIndex] }}</p>
    <p class="gg-note">Class averages are available for each term. Individual learner marks and activity are only available in the latest sample.</p>
    <div class="gg-metric-grid">@foreach ([['Class Average', $progress[$selectedTermIndex].'%', $terms[$selectedTermIndex], ''], ['Change from previous term', ($termChange >= 0 ? '+' : '').$termChange.'%', $selectedTermIndex > 0 ? 'Since '.$terms[$selectedTermIndex - 1] : 'First term', 'green'], ['Students', '32', 'Latest sample', 'orange'], ['Top Performer', '84%', 'Latest sample · Lerato N.', 'blue']] as $metric)<div class="gg-card"><div class="gg-card-label">{{ $metric[0] }}</div><div class="gg-card-value gg-value-{{ $metric[3] }}">{{ $metric[1] }}</div><div class="gg-card-sub">{{ $metric[2] }}</div></div>@endforeach</div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<h2>Class Average Over Time</h2></div><div class="gg-bars">@foreach ($progress as $index => $mark)<div class="gg-bar-row"><span>{{ $terms[$index] }}</span><div class="gg-bar"><span style="width: {{ $mark }}%"></span></div><strong>{{ $mark }}%</strong></div>@endforeach</div></div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'users', 'size' => 20])<h2>Student Breakdown</h2></div><div class="gg-bars">@foreach ($students as $student)<div class="gg-bar-row"><span>{{ $student['name'] }}</span><div class="gg-bar"><span style="width: {{ $student['average'] }}%"></span></div><strong>+{{ $student['change'] }}%</strong></div>@endforeach</div></div>
    <div class="gg-note">@include('partials.icon', ['name' => 'bar-chart', 'size' => 20])<div><strong>Class Insight</strong><p>28 of 32 students met the 10%+ improvement target. Recommend targeted sessions for the 4 below-threshold learners.</p></div></div>
</main>
@endsection
