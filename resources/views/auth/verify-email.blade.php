@extends('layouts.app')

@section('title', 'Verify Your Email | GradeGenius')

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
        <div class="gg-auth-icon">@include('partials.icon', ['name' => 'mail', 'size' => 28])</div>
        <h1>Verify your email</h1>
        <p>We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Verify your email to access your dashboard.</p>
        @if (session('status'))
            <div class="gg-auth-success">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="gg-button gg-button-primary" type="submit">Resend verification email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="gg-button gg-button-surface" type="submit">Sign out</button>
        </form>
    </div>
</main>
@endsection
