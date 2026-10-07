@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-heading', 'Reports Dashboard')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">
            Welcome to GradeGenius Reports
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Monitor academic progress, study activity, and performance.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Average Mark
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                78%
            </p>

            <p class="mt-2 text-xs text-emerald-600">
                +4.2% this term
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Topic Mastery
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                72%
            </p>

            <p class="mt-2 text-xs text-slate-500">
                Across all subjects
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Study Time
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                18.4h
            </p>

            <p class="mt-2 text-xs text-slate-500">
                This week
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Assessments
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-900">
                14
            </p>

            <p class="mt-2 text-xs text-slate-500">
                Completed this term
            </p>
        </div>

    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-6">
                <h3 class="font-semibold text-slate-900">
                    Subject Performance
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Current average by subject.
                </p>
            </div>

            <div class="space-y-5">

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            Mathematics
                        </span>
                        <span class="font-semibold text-slate-900">
                            82%
                        </span>
                    </div>

                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 w-[82%] rounded-full bg-indigo-600"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            Physical Sciences
                        </span>
                        <span class="font-semibold text-slate-900">
                            74%
                        </span>
                    </div>

                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 w-[74%] rounded-full bg-indigo-600"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            English
                        </span>
                        <span class="font-semibold text-slate-900">
                            86%
                        </span>
                    </div>

                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 w-[86%] rounded-full bg-indigo-600"></div>
                    </div>
                </div>

            </div>

        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-6">
                <h3 class="font-semibold text-slate-900">
                    Recent Activity
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Latest learning activity.
                </p>
            </div>

            <div class="space-y-5">

                <div class="flex gap-4">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-600"></div>

                    <div>
                        <p class="text-sm font-medium text-slate-900">
                            Mathematics assessment completed
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Score: 82% · 2 hours ago
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-600"></div>

                    <div>
                        <p class="text-sm font-medium text-slate-900">
                            Physical Sciences study session
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            46 minutes · Yesterday
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-600"></div>

                    <div>
                        <p class="text-sm font-medium text-slate-900">
                            English topic completed
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Comprehension · 2 days ago
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection