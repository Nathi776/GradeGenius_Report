@extends('layouts.app')

@section('title', 'Sign In | GradeGenius')

@section('content')
<main class="gg-auth">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand brand-wrapper">
        <div class="app-icon-badge">
            <img src="{{ asset('images/gradegenius_logo.png') }}" alt="GradeGenius Logo" class="logo-icon">
        </div>
        <div class="logo-text">Grade<span>Genius</span></div>
        <span class="logo-reports-badge">Reports</span>
    </a>
    <div class="gg-auth-corner-toggle">
        @include('partials.theme-toggle')
    </div>
    <div class="gg-auth-card">
        <div class="gg-auth-icon">@include('partials.icon', ['name' => 'arrow-right', 'size' => 28])</div>
        <h1>Welcome back</h1>
        <p>Sign in to access your academic reports dashboard.</p>
        @if ($errors->any())
            <div class="gg-auth-error">{{ $errors->first() }}</div>
        @endif
        @if (session('status'))
            <div class="gg-auth-success">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <a class="gg-auth-link" href="{{ route('password.request') }}">Forgot your password?</a>
            <label class="gg-check"><input type="checkbox" name="remember"> Remember me</label>
            <button class="gg-button gg-button-primary" type="submit">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</button>
        </form>
        <p class="gg-auth-footer">New to GradeGenius? <a href="{{ route('register') }}">Create an account</a></p>
    </div>
</main>
@endsection
