@extends('layouts.app', ['title' => 'Notifications'])

@section('content')
    <div class="mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
        @include('notifications._list', ['notifications' => $notifications, 'unreadCount' => $unreadCount])
    </div>
@endsection
