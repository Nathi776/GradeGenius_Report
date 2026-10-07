@extends('layouts.app')
@section('title', 'Teacher Report | GradeGenius')
@section('content')
@include('partials.report-header')
<main class="gg-report">
    <h1>Class Performance Report</h1><p class="gg-report-intro">Grade 11 Physical Sciences — 2026</p>
    <div class="gg-metric-grid">@foreach ([['Class Average', '71%', 'Term 4', ''], ['Improvement', '+15%', 'Since Term 1', 'green'], ['Students', '32', '', 'orange'], ['Top Performer', '84%', 'Lerato N.', 'blue']] as $metric)<div class="gg-card"><div class="gg-card-label">{{ $metric[0] }}</div><div class="gg-card-value gg-value-{{ $metric[3] }}">{{ $metric[1] }}</div><div class="gg-card-sub">{{ $metric[2] }}</div></div>@endforeach</div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<h2>Class Average Over Time</h2></div><div class="gg-bars">@foreach ($progress as $index => $mark)<div class="gg-bar-row"><span>Term {{ $index + 1 }}</span><div class="gg-bar"><span style="width: {{ $mark }}%"></span></div><strong>{{ $mark }}%</strong></div>@endforeach</div></div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'users', 'size' => 20])<h2>Student Breakdown</h2></div><div class="gg-bars">@foreach ($students as $student)<div class="gg-bar-row"><span>{{ $student['name'] }}</span><div class="gg-bar"><span style="width: {{ $student['average'] }}%"></span></div><strong>+{{ $student['change'] }}%</strong></div>@endforeach</div></div>
    <div class="gg-note">@include('partials.icon', ['name' => 'bar-chart', 'size' => 20])<div><strong>Class Insight</strong><p>28 of 32 students met the 10%+ improvement target. Recommend targeted sessions for the 4 below-threshold learners.</p></div></div>
</main>
@endsection
