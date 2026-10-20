@extends('layouts.app')

@section('title', 'GradeGenius | Turn Grades Into Greatness')

@section('content')
<div class="gg-landing">
<header class="gg-header">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand brand-wrapper">
        <div class="app-icon-badge">
            <img src="{{ asset('images/gradegenius_logo.png') }}" alt="GradeGenius Logo" class="logo-icon">
        </div>
        <div class="logo-text">Grade<span>Genius</span></div>
        <span class="logo-reports-badge">Reports</span>
    </a>
    <nav class="gg-nav" aria-label="Primary navigation">
        <a href="#features">Features</a>
        <a href="#perspectives">For Everyone</a>
        <a href="#stats">Impact</a>
        <a href="#reports">Reports</a>
    </nav>
    <div class="gg-header-actions">
        <a href="https://www.gradegenius.org/" target="_blank" rel="noopener noreferrer" class="gg-nav-link-site">Original Site ↗</a>
        @include('partials.theme-toggle')
        <a href="{{ route('login') }}" class="gg-button gg-button-primary">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
    </div>
</header>

<main>
    <section class="gg-hero">
        <div class="gg-eyebrow gg-eyebrow-pink">@include('partials.icon', ['name' => 'star', 'size' => 14]) South Africa's Academic Intelligence & Reports Portal</div>
        <h1>Turn Grades Into <span>Greatness</span></h1>
        <p>GradeGenius is a comprehensive academic intelligence and reporting system that converts your performance data into personalised strategies — with a <strong>guaranteed minimum 10% improvement</strong> target.</p>
        <div class="gg-actions">
            <a href="{{ route('login') }}" class="gg-button gg-button-primary">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</a>
            <a href="#reports" class="gg-button gg-button-cyan">Explore Reports</a>
        </div>
    </section>

    <section class="gg-dashboard-preview">
        <img class="gg-campus-image" src="{{ asset('images/landing_img.jpg') }}" alt="Graduates celebrating with their caps in the air">
        <div class="gg-preview-metrics">
            @foreach ([['+14.2%', 'Avg. Improvement', 'green'], ['1,847', 'Reports Generated', 'cyan'], ['2,341', 'Active Learners', 'pink']] as $metric)
                <div><strong class="gg-value-{{ $metric[2] }}">{{ $metric[0] }}</strong><span>{{ $metric[1] }}</span></div>
            @endforeach
        </div>
    </section>

    <section class="gg-features" id="features">
        <div class="gg-section-heading">
            <div class="gg-eyebrow gg-eyebrow-cyan">@include('partials.icon', ['name' => 'target', 'size' => 14]) Platform Features</div>
            <h2>Everything You Need to Excel</h2>
            <p>A complete academic intelligence ecosystem — from baseline assessment to exam day readiness.</p>
        </div>
        <div class="gg-feature-grid">
            @foreach ([
                ['file', 'pink', 'Mandatory Assessment Gateway', 'Upload your academic report first. Our engine analyses your baseline and builds a personalised improvement roadmap instantly.'],
                ['trending-up', 'cyan', '10% Performance Target', 'Every learner gets a data-driven strategy engineered to achieve a minimum 10% improvement in academic performance.'],
                ['book-open', 'pink', 'Grade 12 Revision System', '24 years of past exam papers (2002–2025) with auto-marking, timed simulations, and embedded memorandums.'],
                ['sparkles', 'cyan', 'AI-Powered Recommendations', 'Smart study tips, personalised timetables, and targeted interventions generated from your unique performance gaps.'],
                ['bar-chart', 'pink', 'Real-Time Dashboards', 'Live analytics for learners, parents, teachers, and administrators. Everyone sees what matters, when it matters.'],
                ['zap', 'cyan', 'Interactive Learning Tools', 'Digital flashcards, auto-generated quizzes, concept simulations, and matching games — active learning, not passive reading.'],
            ] as $feature)
                <article class="gg-feature-card">
                    <span class="gg-feature-icon gg-feature-icon-{{ $feature[1] }}">
                        @include('partials.icon', ['name' => $feature[0], 'size' => 24])
                    </span>
                    <h3>{{ $feature[2] }}</h3>
                    <p>{{ $feature[3] }}</p>
                </article>
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
        <div class="gg-banner-content">
            <span class="gg-eyebrow gg-eyebrow-banner">@include('partials.icon', ['name' => 'sparkles', 'size' => 14]) Dedicated Reporting System</span>
            <h2>Start with Your Academic Report</h2>
            <p>Every journey on GradeGenius begins with a mandatory report upload. This is how we baseline your performance and build a strategy that actually works for you.</p>
            <div class="gg-banner-actions">
                <a href="{{ route('student.report') }}" class="gg-button gg-button-white">@include('partials.icon', ['name' => 'arrow-right', 'size' => 16]) View Student Reports</a>
                <a href="{{ route('login') }}" class="gg-button gg-button-outline-white">Portal Sign In</a>
            </div>
        </div>
    </section>

    <section class="gg-perspectives" id="perspectives">
        <div class="gg-section-heading">
            <div class="gg-eyebrow gg-eyebrow-pink">@include('partials.icon', ['name' => 'users', 'size' => 14]) Built For Everyone</div>
            <h2>One Platform, Four Perspectives</h2>
            <p>Tailored dashboards and tools for every role in the educational ecosystem.</p>
        </div>
        <div class="gg-role-grid">
            @foreach ([
                ['Learner', 'graduation-cap', 'cyan', ['Personal performance dashboard', 'Personalised study roadmap', 'Past papers with auto-marking', 'Interactive flashcards & quizzes', 'Digital scientific calculator']],
                ['Parent', 'users', 'pink', ['Real-time academic monitoring', 'Homework submission alerts', 'Early risk notifications', 'Progress trend reports', 'Engagement indicators']],
                ['Teacher', 'book-open', 'magenta', ['Digital homework assignment', 'Class-wide analytics', 'Topic weakness identification', 'Automated marking support', 'Exportable performance reports']],
                ['Administrator', 'bar-chart', 'purple', ['School-wide performance insights', 'Benchmarking analytics', 'Dropout risk detection', 'Strategic trend reporting', 'Compliance documentation']]
            ] as $role)
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
</div>
@endsection
