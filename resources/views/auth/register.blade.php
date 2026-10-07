@extends('layouts.app')

@section('title', 'Create Account | GradeGenius')

@section('content')
<main class="gg-auth">
    <a href="{{ route('dashboard.landing') }}" class="gg-brand">
        <span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap'])</span>
        GradeGenius
    </a>
    <div class="gg-auth-card">
        <div class="gg-auth-icon">@include('partials.icon', ['name' => 'users', 'size' => 28])</div>
        <h1>Create your account</h1>
        <p>Choose your role and start using GradeGenius.</p>
        @if ($errors->any())
            <div class="gg-auth-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            <label for="role">I am registering as</label>
            <select id="role" name="role" required>
                <option value="">Select a role</option>
                <option value="student" @selected(old('role') === 'student')>Learner</option>
                <option value="parent" @selected(old('role') === 'parent')>Parent</option>
                <option value="teacher" @selected(old('role') === 'teacher')>Teacher</option>
            </select>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            <button class="gg-button gg-button-blue" type="submit">Create account @include('partials.icon', ['name' => 'arrow-right', 'size' => 16])</button>
        </form>
        <p class="gg-auth-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</main>
@endsection
