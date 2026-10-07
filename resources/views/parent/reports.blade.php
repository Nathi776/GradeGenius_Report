@extends('layouts.app')

@section('title', 'Parent Reports')

@section('page-heading', 'Parent Reports')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">
            Child Progress
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Monitor your child's academic progress and learning activity.
        </p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <h3 class="font-semibold text-slate-900">
            Student Overview
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Parent reporting information will be displayed here.
        </p>

    </div>

</div>

@endsection