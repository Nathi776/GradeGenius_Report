@extends('layouts.app')

@section('title', 'Choose New Password | GradeGenius')

@section('content')
<main class="gg-auth">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand brand-wrapper">
        <div class="app-icon-badge">
            <img src="{{ asset('images/gradegenius_logo.png') }}" alt="GradeGenius Logo" class="logo-icon">
        </div>
        <div class="logo-text">Grade<span>Genius</span></div>
        <span class="logo-reports-badge">Reports</span>
    </a>
    <div class="gg-auth-card">
        <div class="gg-auth-icon">@include('partials.icon', ['name' => 'arrow-right', 'size' => 28])</div>
        <h1>Choose a new password</h1>
        <p>Your new password must contain at least 8 characters.</p>
        @if ($errors->any())
            <div class="gg-auth-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            <button class="gg-button gg-button-primary" type="submit">Reset password</button>
        </form>
    </div>
</main>
@endsection
