@extends('layouts.app')

@section('title', 'Teacher Reports')

@section('page-heading', 'Teacher Reports')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">
            Class Reports
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Monitor student performance, engagement and academic progress.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Students</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">32</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Class Average</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">71%</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Active Students</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">27</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">At Risk</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">5</p>
        </div>

    </div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <h3 class="font-semibold text-slate-900">
            Class Performance
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Teacher reporting information will be displayed here.
        </p>

    </div>

</div>

@endsection