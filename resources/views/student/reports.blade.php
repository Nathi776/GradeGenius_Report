@extends('layouts.app')

@section('title', 'Student Reports')

@section('page-heading', 'Student Reports')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">
            My Academic Report
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Track your academic performance, mastery and study activity.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Overall Average</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">78%</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Topic Mastery</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">72%</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Study Time</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">18.4h</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Assessments</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">14</p>
        </div>

    </div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <h3 class="font-semibold text-slate-900">
            Your Progress
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Detailed student reporting will be displayed here.
        </p>

    </div>

</div>

@endsection