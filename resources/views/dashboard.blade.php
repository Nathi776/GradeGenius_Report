@extends('layouts.app')

@section('title', 'GradeGenius | Turn Grades Into Greatness')

@section('content')
<header class="gg-header">
    <a href="{{ route('dashboard') }}" class="gg-brand">
        <span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap', 'size' => 20])</span>
        <span>GradeGenius</span>
    </a>
    <nav class="gg-nav" aria-label="Primary navigation">
        <a href="#features">Features</a>
        <a href="#perspectives">For Everyone</a>
        <a href="#stats">Impact</a>
    </nav>
    <a href="#reports" class="gg-button gg-button-blue">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
</header>

<main>
    <section class="gg-hero">
        <div class="gg-eyebrow">@include('partials.icon', ['name' => 'star', 'size' => 14]) South Africa's Academic Intelligence Platform</div>
        <h1>Turn Grades Into <span>Greatness</span></h1>
        <p>GradeGenius is a comprehensive academic intelligence system that converts your performance data into personalised strategies — with a <strong>guaranteed minimum 10% improvement</strong> target.</p>
        <div class="gg-actions">
            <a href="#reports" class="gg-button gg-button-blue">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
            <a href="#features" class="gg-button gg-button-surface">Explore Features</a>
        </div>
    </section>

    <section class="gg-dashboard-preview" id="features">
        <div class="gg-campus-image"></div>
        <div class="gg-preview-metrics">
            @foreach ([['+14.2%', 'Avg. Improvement', 'green'], ['1,847', 'Papers Completed', 'blue'], ['2,341', 'Active Learners', 'orange']] as $metric)
                <div><strong class="gg-value-{{ $metric[2] }}">{{ $metric[0] }}</strong><span>{{ $metric[1] }}</span></div>
            @endforeach
        </div>
    </section>

    <section class="gg-stats" id="stats">
        @foreach ([['trending-up', '10%+', 'Minimum performance gain'], ['clock', '24', 'Years of past exam papers'], ['users', '4', 'Role-based dashboards'], ['graduation-cap', '100%', 'Curriculum-aligned content']] as $stat)
            <div class="gg-stat">
                <span class="gg-stat-icon">@include('partials.icon', ['name' => $stat[0], 'size' => 30])</span>
                <strong>{{ $stat[1] }}</strong>
                <span>{{ $stat[2] }}</span>
            </div>
        @endforeach
    </section>

    <section class="gg-banner" id="reports">
        <h2>Start with Your Academic Report</h2>
        <p>Every journey on GradeGenius begins with a mandatory report upload. This is how we baseline your performance and build a strategy that actually works for you.</p>
        <a href="{{ route('student.reports') }}" class="gg-button gg-button-white">@include('partials.icon', ['name' => 'arrow-right', 'size' => 16]) View Reports</a>
    </section>

    <section class="gg-perspectives" id="perspectives">
        <div class="gg-section-heading">
            <div class="gg-eyebrow gg-eyebrow-blue">@include('partials.icon', ['name' => 'users', 'size' => 14]) Built For Everyone</div>
            <h2>One Platform, Four Perspectives</h2>
            <p>Tailored dashboards and tools for every role in the educational ecosystem.</p>
        </div>
        <div class="gg-role-grid">
            @foreach ([['Learner', 'graduation-cap', 'blue', route('student.reports')], ['Parent', 'users', 'rust', route('parent.reports')], ['Teacher', 'book-open', 'blue', route('teacher.reports')], ['Admin', 'bar-chart', 'rust', '#']] as $role)
                <a href="{{ $role[3] }}" class="gg-role-card gg-role-{{ $role[2] }}">
                    @include('partials.icon', ['name' => $role[1], 'size' => 32])
                    <span>{{ $role[0] }} @include('partials.icon', ['name' => 'arrow-right', 'size' => 20])</span>
                </a>
            @endforeach
        </div>
    </section>
</main>
@endsection
