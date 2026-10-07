@extends('layouts.app')

@section('title', 'Sign In | GradeGenius')

@section('content')
<main class="gg-auth">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand">
        <span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap'])</span>
        GradeGenius
    </a>
    <div class="gg-auth-card">
        <div class="gg-auth-icon">@include('partials.icon', ['name' => 'arrow-right', 'size' => 28])</div>
        <h1>Welcome back</h1>
        <p>Sign in to access your academic dashboard.</p>
        @if ($errors->any())
            <div class="gg-auth-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <label class="gg-check"><input type="checkbox" name="remember"> Remember me</label>
            <button class="gg-button gg-button-blue" type="submit">Sign In @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</button>
        </form>
        <p class="gg-auth-footer">New to GradeGenius? <a href="{{ route('register') }}">Create an account</a></p>
    </div>
</main>
@endsection
