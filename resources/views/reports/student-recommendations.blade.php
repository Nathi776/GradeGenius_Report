@extends('layouts.student', ['title' => 'Learning Recommendations', 'active' => 'recommendations'])

@php
    $summary = $plan['summary'];
    $criteria = $plan['criteria'];
    $card = 'rounded-2xl border border-line bg-card p-5 sm:p-6';
    $reportUrl = route('student.report');
@endphp

@section('student-content')
    <div class="space-y-5">
        <header>
            <h1 class="text-3xl font-normal tracking-tight text-ink sm:text-4xl">Learning recommendations</h1>
            <p class="mt-2 text-muted">Grade {{ $student['grade'] }} · What to revise first, and why</p>
        </header>

        <section aria-label="Recommendation summary" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat-tile label="Topics to revise" :value="$summary['weak_topics']" :sub="'Below '.$criteria['weak_below'].'% mastery'" />
            <x-stat-tile label="High priority" :value="$summary['high_priority']" :sub="'Under '.$criteria['high_below'].'% or exam close'" tone="peach" />
            <x-stat-tile label="Subjects involved" :value="$summary['subjects']" sub="With topics to revise" />
            <x-stat-tile :label="'Exams within '.$criteria['exam_soon_days'].' days'" :value="$summary['exams_soon']" sub="From the exam schedule" />
        </section>

        <div class="grid gap-5 lg:grid-cols-12">
            <section aria-labelledby="priority-heading" class="{{ $card }} lg:col-span-8">
                <h2 id="priority-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                    @include('partials.icon', ['name' => 'sparkles', 'size' => 20])
                    Revise in this order
                </h2>
                <p class="mt-1 text-sm text-muted">Ranked by how far each topic is below {{ $criteria['weak_below'] }}%. A topic moves up when an exam for that subject is close. Every item shows the reasons.</p>

                @if (count($plan['items']) > 0)
                    @php
                        $visibleCount = 5;
                        $firstItems = array_slice($plan['items'], 0, $visibleCount);
                        $remainingItems = array_slice($plan['items'], $visibleCount);
                    @endphp
                    @foreach ([$firstItems, $remainingItems] as $groupIndex => $group)
                        @if (count($group) > 0)
                            @if ($groupIndex === 1)
                                <details class="group mt-3">
                                    <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-blue focus-visible:outline-2 focus-visible:outline-blue">
                                        <span class="group-open:hidden">Show {{ count($remainingItems) }} more topics</span>
                                        <span class="hidden group-open:inline">Show fewer topics</span>
                                    </summary>
                            @endif
                            <ol class="mt-4 space-y-3">
                                @foreach ($group as $item)
                                    <li>
                                        <x-action-card :title="$item['topic']" :subject="$item['subject']" :mastery="$item['mastery']" :priority="$item['priority']" :tone="$item['tone']" :color="$item['color']" :href="$reportUrl.'#subject-'.$item['subject_key']" actionText="View detail">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Why this is on your list</p>
                                            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-sm text-ink">
                                                @foreach ($item['reasons'] as $reason)
                                                    <li>{{ $reason }}</li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-muted">Suggested approach</p>
                                            <p class="mt-1 text-sm text-ink">{{ $item['approach'] }}</p>
                                        </x-action-card>
                                    </li>
                                @endforeach
                            </ol>
                            @if ($groupIndex === 1)
                                </details>
                            @endif
                        @endif
                    @endforeach
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-line p-4 text-sm text-muted">No topic is below {{ $criteria['weak_below'] }}% mastery right now. Keep practising to stay on track.</p>
                @endif
            </section>

            <aside class="space-y-5 lg:col-span-4">
                <section aria-labelledby="exams-heading" class="{{ $card }}">
                    <h2 id="exams-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'calendar', 'size' => 20])
                        Upcoming exams
                    </h2>
                    @if (count($exams) > 0)
                        <ul class="mt-3 divide-y divide-line/70">
                            @foreach ($exams as $exam)
                                <li class="flex items-center justify-between gap-3 py-3">
                                    <div class="min-w-0">
                                        <p class="font-medium text-ink">{{ $exam['subject'] }}</p>
                                        <p class="mt-0.5 text-xs text-muted">{{ $exam['title'] }}@if ($exam['paper']) · {{ $exam['paper'] }}@endif · {{ $exam['date_label'] }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold {{ $exam['days_until'] <= $criteria['exam_soon_days'] ? 'text-warn' : 'text-muted' }}">{{ $exam['countdown'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-muted">No exam schedule is available yet. Once exams are added, topics in those subjects move up the list as the date gets close.</p>
                    @endif
                </section>

                <section aria-labelledby="planner-heading" class="{{ $card }}">
                    <h2 id="planner-heading" class="flex items-center gap-2 text-lg font-normal text-ink">
                        @include('partials.icon', ['name' => 'check-circle', 'size' => 20])
                        Study planner
                    </h2>
                    <p class="mt-3 text-sm text-muted">Turn this list into a week of short revision sessions.</p>
                    <a href="{{ route('student.planner') }}" class="mt-3 inline-flex items-center rounded-lg border border-line bg-card-2 px-3 py-1.5 text-sm font-medium text-ink hover:border-blue focus-visible:outline-2 focus-visible:outline-blue">Open the planner</a>
                </section>
            </aside>
        </div>
    </div>
@endsection
