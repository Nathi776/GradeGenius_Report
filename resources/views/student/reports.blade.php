@extends('layouts.app')
@section('title', 'Student Report | GradeGenius')
@section('content')
@include('partials.report-header')
<main class="gg-report">
    <h1>Your Academic Report</h1><p class="gg-report-intro">Track your marks and progress across the year.</p>
    <div class="gg-metric-grid">
        @foreach ([['Current Average', '74%', 'Term 4', ''], ['Improvement', '+16%', 'Since Term 1', 'green'], ['Subjects', '5', '', 'orange'], ['Target Gap', 'On track', '10%+ goal met', 'blue']] as $metric)
            <div class="gg-card"><div class="gg-card-label">{{ $metric[0] }}</div><div class="gg-card-value gg-value-{{ $metric[3] }}">{{ $metric[1] }}</div><div class="gg-card-sub">{{ $metric[2] }}</div></div>
        @endforeach
    </div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<h2>Progress Over Time</h2></div><div class="gg-bars">@foreach ($progress as $item)<div class="gg-bar-row"><span>{{ $item['term'] }}</span><div class="gg-bar"><span style="width: {{ $item['mark'] }}%"></span></div><strong>{{ $item['mark'] }}%</strong></div>@endforeach</div></div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'book-open', 'size' => 20])<h2>Subject Breakdown</h2></div><div class="gg-bars">@foreach ($subjects as $subject)<div class="gg-bar-row"><span>{{ $subject['name'] }}<small class="gg-card-sub">Grade {{ $subject['grade'] }}</small></span><div class="gg-bar"><span style="width: {{ $subject['mark'] }}%"></span></div><strong>{{ $subject['mark'] }}%</strong></div>@endforeach</div></div>
    <div class="gg-note">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<div><strong>Strategy Recommendation</strong><p>Focus revision on Life Sciences past papers (2018–2024). Projected gain: +8% to reach 82%.</p></div></div>
</main>
@endsection
