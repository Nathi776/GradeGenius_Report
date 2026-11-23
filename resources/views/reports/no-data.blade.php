@php
    $heading = $isParent
        ? ($reason === 'no_children' ? 'No confirmed children yet' : 'No marks recorded yet')
        : 'No marks recorded yet';
    $message = match (true) {
        $isParent && $reason === 'no_children' => 'Once a learner confirms your link, or you confirm theirs, their report will appear here.',
        $isParent => 'Your child\'s teachers have not recorded any marks yet. Their report will appear here as soon as they do.',
        default => 'Your teachers have not recorded any marks for you yet. Your report, recommendations and planner will fill in as soon as they do.',
    };
@endphp

@extends($isParent ? 'layouts.parent' : 'layouts.student', ['title' => $heading, 'active' => $isParent ? 'home' : 'dashboard'])

@section('parent-content')
    @include('reports.partials.no-data-card', ['heading' => $heading, 'message' => $message])
@endsection

@section('student-content')
    @include('reports.partials.no-data-card', ['heading' => $heading, 'message' => $message])
@endsection
