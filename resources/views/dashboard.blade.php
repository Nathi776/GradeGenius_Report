@extends('layouts.app')

@section('title', 'GradeGenius | Turn Grades Into Greatness')

@section('content')
<header class="gg-header">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand">
        <span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap', 'size' => 20])</span>
        <span>GradeGenius</span>
    </a>
    <nav class="gg-nav" aria-label="Primary navigation">
        <a href="#features">Features</a>
        <a href="#perspectives">For Everyone</a>
        <a href="#stats">Impact</a>
    </nav>
    <a href="{{ route('login') }}" class="gg-button gg-button-blue">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
</header>

<main>
    <section class="gg-hero">
        <div class="gg-eyebrow">@include('partials.icon', ['name' => 'star', 'size' => 14]) South Africa's Academic Intelligence Platform</div>
        <h1>Turn Grades Into <span>Greatness</span></h1>
        <p>GradeGenius is a comprehensive academic intelligence system that converts your performance data into personalised strategies — with a <strong>guaranteed minimum 10% improvement</strong> target.</p>
        <div class="gg-actions">
            <a href="{{ route('login') }}" class="gg-button gg-button-blue">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
            <a href="#features" class="gg-button gg-button-surface">Explore Features</a>
        </div>
    </section>

    <section class="gg-dashboard-preview" id="features">
        <img class="gg-campus-image" src="{{ asset('images/landing_img.jpg') }}" alt="Graduates celebrating with their caps in the air">
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
            @foreach ([['Learner', 'graduation-cap', 'blue', ['Personal performance dashboard', 'Personalised study roadmap', 'Past papers with auto-marking', 'Interactive flashcards & quizzes', 'Digital scientific calculator']], ['Parent', 'users', 'rust', ['Real-time academic monitoring', 'Homework submission alerts', 'Early risk notifications', 'Progress trend reports', 'Engagement indicators']], ['Teacher', 'book-open', 'blue', ['Digital homework assignment', 'Class-wide analytics', 'Topic weakness identification', 'Automated marking support', 'Exportable performance reports']], ['Administrator', 'bar-chart', 'rust', ['School-wide performance insights', 'Benchmarking analytics', 'Dropout risk detection', 'Strategic trend reporting', 'Compliance documentation']]] as $role)
                <div class="gg-role-card gg-role-{{ $role[2] }}">
                    @include('partials.icon', ['name' => $role[1], 'size' => 32])
                    <span>{{ $role[0] }}</span>
                    <ul>
                        @foreach ($role[3] as $feature)
                            <li>@include('partials.icon', ['name' => 'check-circle', 'size' => 15]) {{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
</main>
@endsection
