@extends('layouts.teacher', ['title' => 'Teacher dashboard', 'active' => 'home'])

@php
    $snap = $dashboard['snapshot'];
    $criteria = $dashboard['criteria'];
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad'];
    $dot = ['good' => 'bg-good', 'mid' => 'bg-warn', 'low' => 'bg-bad'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-white hover:border-blue focus-visible:outline-2 focus-visible:outline-blue';
    $soon = 'shrink-0 whitespace-nowrap rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted';
    $pts = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')).abs($n).' pts';
    $trendSeries = [['key' => 'overall', 'name' => 'Class average', 'color' => '#f8fafc', 'points' => $dashboard['trend']['points'], 'area' => true, 'labels' => true]];
@endphp

@section('teacher-content')
    <div class="space-y-5">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl font-normal tracking-tight sm:text-4xl">Welcome back, {{ $dashboard['teacher']['name'] }}</h1>
                <p class="mt-2 text-muted">{{ $snap['class_count'] }} {{ $snap['class_count'] === 1 ? 'class' : 'classes' }} · {{ $snap['students'] }} learners</p>
            </div>
            <span class="{{ $soon }}">Demo data</span>
        </header>

        <div class="grid gap-5 lg:grid-cols-12">
            {{-- 1. Snapshot --}}
            <section aria-label="Class snapshot" class="grid grid-cols-2 gap-3 lg:col-span-12 lg:grid-cols-4">
                <x-stat-tile label="Total learners" :value="$snap['students']" :sub="'Across '.$snap['class_count'].' classes'" />
                <x-stat-tile label="Class average" :value="$snap['average'].'%'" :sub="$pts($snap['change']).' since '.$snap['first_term']" :tone="$snap['change'] >= 0 ? 'good' : 'low'" />
                <x-stat-tile label="For review" :value="$snap['at_risk']" :sub="'Meet the review criteria below'" tone="peach" />
                <x-stat-tile label="Top performers" :value="$snap['top_count']" :sub="'Averaging '.$criteria['top_at_least'].'% or more'" tone="sky" />
            </section>

            {{-- 2. Trend --}}
            <section aria-labelledby="trend-heading" class="{{ $card }} lg:col-span-7">
                <h2 id="trend-heading" class="flex items-center gap-2 text-lg font-normal">
                    @include('partials.icon', ['name' => 'trending-up', 'size' => 20])
                    Class performance trend
                </h2>
                <p class="mt-1 text-sm text-muted">Average of every learner across all your classes, term by term.</p>
                <div class="mt-4">
                    <x-line-chart :series="$trendSeries" :labels="$dashboard['trend']['labels']" id="teacher-trend" :height="220" />
                </div>
            </section>

            {{-- 3. My classes --}}
            <section aria-labelledby="classes-heading" class="{{ $card }} lg:col-span-5">
                <h2 id="classes-heading" class="flex items-center gap-2 text-lg font-normal">
                    @include('partials.icon', ['name' => 'users', 'size' => 20])
                    My classes
                </h2>
                <p class="mt-1 text-sm text-muted">Latest average against each class target.</p>
                @if (count($dashboard['classes']) > 0)
                    <ul class="mt-4 space-y-4">
                        @foreach ($dashboard['classes'] as $class)
                            <li>
                                <x-bar-row :label="$class['subject']" :value="$class['average']" :tone="$class['tone']" />
                                <p class="mt-1 text-xs text-muted">{{ $class['name'] }} · {{ $class['learner_count'] }} learners · target {{ $class['target'] }}%@if ($class['attention'] > 0) · <span class="text-warn">{{ $class['attention'] }} to review</span>@endif</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">No classes are assigned to you yet.</p>
                @endif
            </section>

            {{-- 4. Learners to review: every flag shows its reason --}}
            <section aria-labelledby="review-heading" class="{{ $card }} lg:col-span-7">
                <h2 id="review-heading" class="flex items-center gap-2 text-lg font-normal">
                    @include('partials.icon', ['name' => 'target', 'size' => 20])
                    Learners to review
                </h2>
                <p class="mt-1 text-sm text-muted">Shown when a learner's average is below {{ $criteria['risk_below'] }}% or has dropped {{ $criteria['decline_points'] }} or more points since last term. A flag means "take a look", not a judgement of ability.</p>
                @if (count($dashboard['review']) > 0)
                    <ul class="mt-4 space-y-3">
                        @foreach ($dashboard['review'] as $learner)
                            <li class="rounded-xl border border-line bg-card-2 p-4">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="font-medium text-white">{{ $learner['name'] }}</span>
                                    <span class="tabular-nums text-white">{{ $learner['average'] }}%</span>
                                    <span class="text-sm text-muted">{{ $learner['class'] }}</span>
                                </div>
                                <ul class="mt-1 list-disc pl-5 text-sm text-warn">
                                    @foreach ($learner['reasons'] as $reason)
                                        <li>{{ $reason }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">No learner currently meets the review criteria.</p>
                @endif
            </section>

            {{-- 5. Insights: derived only from the figures above --}}
            <section aria-labelledby="insights-heading" class="{{ $card }} lg:col-span-5">
                <h2 id="insights-heading" class="flex items-center gap-2 text-lg font-normal">
                    @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                    Learner insights
                </h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($dashboard['insights'] as $insight)
                        <li class="flex items-start gap-3 text-sm text-slate-200">
                            <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $dot[$insight['tone']] }}" aria-hidden="true"></span>
                            <span>{{ $insight['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- 6. Not built yet: honest empty states --}}
            <section aria-labelledby="exams-heading" class="{{ $card }} lg:col-span-6">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="exams-heading" class="flex items-center gap-2 text-lg font-normal">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 20])
                        Upcoming assessments
                    </h2>
                    <span class="{{ $soon }}">Coming soon</span>
                </div>
                <p class="mt-4 text-sm text-muted">No assessments scheduled yet. Quizzes and tests will appear here once assessment scheduling is connected.</p>
            </section>

            <section aria-labelledby="activity-heading" class="{{ $card }} lg:col-span-6">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="activity-heading" class="flex items-center gap-2 text-lg font-normal">
                        @include('partials.icon', ['name' => 'zap', 'size' => 20])
                        Recent activity
                    </h2>
                    <span class="{{ $soon }}">Coming soon</span>
                </div>
                <p class="mt-4 text-sm text-muted">Quiz submissions and past papers will show up here once learner assessment records are connected.</p>
            </section>
        </div>
    </div>
@endsection
