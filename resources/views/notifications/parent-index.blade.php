@extends('layouts.parent', ['title' => 'Notifications', 'active' => 'notifications'])

@section('parent-content')
    @include('notifications._list', ['notifications' => $notifications, 'unreadCount' => $unreadCount])
@endsection
