@extends('layouts.teacher', ['title' => 'Notifications', 'active' => 'notifications'])

@section('teacher-content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @include('notifications._list', ['notifications' => $notifications, 'unreadCount' => $unreadCount])
    </div>
@endsection
