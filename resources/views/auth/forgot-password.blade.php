@extends('layouts.app')

@section('title', 'Forgot Password | GradeGenius')

@section('content')
<main class="gg-auth">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand">
        <span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap'])</span>
        GradeGenius
    </a>
    <div class="gg-auth-card">
        <div class="gg-auth-icon">@include('partials.icon', ['name' => 'mail', 'size' => 28])</div>
        <h1>Reset your password</h1>
        <p>Enter your email address and we will send you a reset link.</p>
        @if ($errors->any())
            <div class="gg-auth-error">{{ $errors->first() }}</div>
        @endif
        @if (session('status'))
            <div class="gg-auth-success">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            <button class="gg-button gg-button-blue" type="submit">Email reset link</button>
        </form>
        <p class="gg-auth-footer"><a href="{{ route('login') }}">Return to sign in</a></p>
    </div>
</main>
@endsection
