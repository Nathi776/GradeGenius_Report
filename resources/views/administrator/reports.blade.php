@extends('layouts.administrator', ['title' => 'Executive Academic Dashboard', 'active' => 'overview'])

@php
    $snap = $dashboard['snapshot'];
    $text = ['good' => 'text-good', 'mid' => 'text-warn', 'low' => 'text-bad', 'sky' => 'text-sky'];
    $bg = ['good' => 'bg-good', 'mid' => 'bg-warn', 'low' => 'bg-bad', 'sky' => 'bg-sky'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $link = 'inline-flex items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-brand/40 transition focus-visible:outline-2 focus-visible:outline-blue';
    $pts = fn ($n) => ($n > 0 ? '+' : ($n < 0 ? '−' : '')).abs($n).' pts';
    $trendSeries = [['key' => 'overall', 'name' => 'School average', 'color' => '#f23d99', 'points' => $dashboard['trend']['points'], 'area' => true, 'labels' => true]];
@endphp

@section('admin-content')
    @include('partials.print-header', ['title' => 'School-wide Academic Intelligence & Leadership Report'])

    <div class="space-y-5">
        <header class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl font-normal tracking-tight text-ink sm:text-4xl">Executive Academic Dashboard</h1>
                <p class="mt-2 text-muted">School-wide academic performance, NSC matric projections, and departmental intelligence.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="shrink-0 whitespace-nowrap rounded-full border border-line px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted">Demo data</span>
                <span class="inline-flex items-center gap-2 rounded-full border border-line bg-card-2 px-3 py-1 text-sm text-ink">
                    <span class="text-muted">Total Learners</span>
                    <strong class="font-semibold text-ink">{{ number_format($snap['total_learners']) }}</strong>
                </span>
            </div>
        </header>

        {{-- 1. High-Level School KPIs --}}
        <section aria-label="Executive snapshot" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat-tile label="School Average" :value="$snap['school_average'].'%'" :sub="$pts($snap['change']).' since '.$snap['first_term']" :tone="$snap['change'] >= 0 ? 'good' : 'low'" />
            <x-stat-tile label="Projected Pass Rate" :value="$snap['pass_rate'].'%'" sub="Curriculum benchmark: 90%" tone="good" />
            <x-stat-tile label="Bachelor Pass Rate" :value="$snap['bachelor_pass_rate'].'%'" sub="University exemption track" tone="sky" />
            <x-stat-tile label="Intervention Focus" :value="$snap['at_risk_count']" sub="Learners flagged for review" tone="peach" />
        </section>

        <div class="grid gap-5 lg:grid-cols-12">
            {{-- 2. Performance Trend Across Terms --}}
            <section aria-labelledby="trend-heading" class="{{ $card }} lg:col-span-7">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="trend-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                            @include('partials.icon', ['name' => 'trending-up', 'size' => 20])
                            School-wide Academic Trajectory
                        </h2>
                        <p class="mt-1 text-sm text-muted">Composite term average across all enrolled learners and grades.</p>
                    </div>
                    <span class="rounded-full border border-line bg-card-2 px-2.5 py-1 text-xs font-semibold text-good">+{{ $snap['change'] }}% Year-to-Date</span>
                </div>
                <div class="mt-4">
                    <x-line-chart :series="$trendSeries" :labels="$dashboard['trend']['labels']" id="admin-school-trend" :height="220" />
                </div>
            </section>

            {{-- 3. CAPS Achievement Level Distribution --}}
            <section id="levels-heading" aria-labelledby="levels-title" class="{{ $card }} lg:col-span-5">
                <h2 id="levels-title" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'bar-chart', 'size' => 20])
                    CAPS Achievement Distribution
                </h2>
                <p class="mt-1 text-sm text-muted">Breakdown by National Department of Basic Education achievement scale.</p>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($dashboard['levels'] as $lvl)
                        <li>
                            <div class="flex items-center justify-between text-xs font-medium text-ink">
                                <span>{{ $lvl['label'] }}</span>
                                <span class="tabular-nums font-semibold">{{ $lvl['count'] }} <span class="text-muted">({{ $lvl['percentage'] }}%)</span></span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-line" role="presentation">
                                <div class="h-full rounded-full {{ $bg[$lvl['tone']] }}" style="width: {{ $lvl['percentage'] }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- 4. Grade Cohort Breakdown --}}
            <section id="cohorts-heading" aria-labelledby="cohorts-title" class="{{ $card }} lg:col-span-12">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="cohorts-title" class="flex items-center gap-2 text-lg font-normal text-ink">
                            @include('partials.icon', ['name' => 'users', 'size' => 20])
                            Cohort Performance by Grade
                        </h2>
                        <p class="mt-1 text-sm text-muted">Comparative academic standing and matric readiness indicators.</p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm text-ink">
                        <thead>
                            <tr class="border-b border-line text-xs uppercase tracking-wider text-muted">
                                <th class="pb-3 font-semibold">Cohort</th>
                                <th class="pb-3 font-semibold">Learners</th>
                                <th class="pb-3 font-semibold">Term Average</th>
                                <th class="pb-3 font-semibold">Target</th>
                                <th class="pb-3 font-semibold">Pass Rate</th>
                                <th class="pb-3 font-semibold">Bachelor %</th>
                                <th class="pb-3 font-semibold text-right">Review Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/70">
                            @foreach ($dashboard['cohorts'] as $c)
                                <tr class="hover:bg-card-2/50 transition">
                                    <td class="py-3 font-medium text-ink">{{ $c['grade'] }}</td>
                                    <td class="py-3 tabular-nums text-muted">{{ $c['learners'] }}</td>
                                    <td class="py-3 font-semibold tabular-nums text-ink">{{ $c['average'] }}%</td>
                                    <td class="py-3 tabular-nums text-muted">{{ $c['target'] }}%</td>
                                    <td class="py-3 tabular-nums font-medium text-good">{{ $c['pass_rate'] }}%</td>
                                    <td class="py-3 tabular-nums text-sky font-medium">{{ $c['bachelor_rate'] }}%</td>
                                    <td class="py-3 text-right">
                                        @if ($c['attention'] > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-card-2 px-2.5 py-0.5 text-xs font-semibold text-warn">
                                                {{ $c['attention'] }} to review
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-good/30 bg-good/10 px-2.5 py-0.5 text-xs font-semibold text-good">
                                                On track
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- 5. Academic Departments Standing --}}
            <section id="departments-heading" aria-labelledby="departments-title" class="{{ $card }} lg:col-span-7">
                <h2 id="departments-title" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'book-open', 'size' => 20])
                    Departmental Benchmarks
                </h2>
                <p class="mt-1 text-sm text-muted">Subject-level pass rates and performance relative to departmental targets.</p>

                <div class="mt-4 space-y-4">
                    @foreach ($dashboard['departments'] as $dept)
                        <div class="rounded-xl border border-line bg-card-2 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-medium text-ink">{{ $dept['name'] }}</p>
                                    <p class="text-xs text-muted">Lead: {{ $dept['head'] }} · Pass Rate {{ $dept['pass_rate'] }}%</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-semibold tabular-nums text-ink">{{ $dept['average'] }}%</span>
                                    <span class="text-xs block text-muted">Goal {{ $dept['target'] }}% ({{ $pts($dept['change']) }})</span>
                                </div>
                            </div>
                            <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-line" role="presentation">
                                <div class="h-full rounded-full {{ $bg[$dept['tone']] }}" style="width: {{ $dept['average'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 6. Strategic Interventions & Actions --}}
            <section id="interventions-heading" aria-labelledby="interventions-title" class="{{ $card }} lg:col-span-5">
                <h2 id="interventions-title" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'target', 'size' => 20])
                    Targeted Academic Interventions
                </h2>
                <p class="mt-1 text-sm text-muted">Remedial actions deployed to safeguard matric pass rates.</p>

                <div class="mt-4 space-y-3">
                    @foreach ($dashboard['interventions'] as $item)
                        <div class="rounded-xl border border-line bg-card-2 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-ink text-sm">{{ $item['title'] }}</p>
                                <span class="rounded-full border border-brand/40 bg-brand/10 px-2 py-0.5 text-[10px] font-semibold text-brand">{{ $item['status'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-muted">{{ $item['cohort'] }}</p>
                            <p class="mt-2 text-xs text-ink-sub">{{ $item['impact'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
