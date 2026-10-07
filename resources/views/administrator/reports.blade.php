@extends('layouts.app')
@section('title', 'Administrator Dashboard | GradeGenius')
@section('content')
@include('partials.report-header')
<main class="gg-report">
    <h1>Administrator Dashboard</h1><p class="gg-report-intro">School-wide academic performance and strategic insights.</p>
    <div class="gg-metric-grid">@foreach ([['Active learners', '2,341'], ['Average improvement', '+14.2%'], ['At-risk learners', '86'], ['Reports generated', '1,847']] as $metric)<div class="gg-card"><div class="gg-card-label">{{ $metric[0] }}</div><div class="gg-card-value">{{ $metric[1] }}</div></div>@endforeach</div>
    <div class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'bar-chart', 'size' => 20])<h2>Strategic Overview</h2></div><p class="gg-card-sub">Monitor school-wide performance, benchmark results, dropout risk, and compliance documentation from one administrative dashboard.</p></div>
</main>
@endsection
