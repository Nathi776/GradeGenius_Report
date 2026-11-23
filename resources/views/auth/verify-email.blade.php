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
        <h1>Enter your verification code</h1>
        <p>We emailed a {{ $pinLength }}-digit code to <strong>{{ auth()->user()->email }}</strong>. Type it below to continue; you can stay in this browser.</p>
        @if (session('status'))
            <div class="gg-auth-success">{{ session('status') }}</div>
        @endif
        @error('pin')
            <div class="gg-auth-error" role="alert">{{ $message }}</div>
        @enderror
        <form method="POST" action="{{ route('verification.verify') }}">
            @csrf
            <label for="pin">Verification code</label>
            <input id="pin" name="pin" type="text" class="gg-pin-input" inputmode="numeric" autocomplete="one-time-code"
                   maxlength="{{ $pinLength }}" pattern="\d{6}" placeholder="000000" required autofocus>
            <button class="gg-button gg-button-primary" type="submit">Verify and continue</button>
        </form>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="gg-button gg-button-surface" type="submit" id="resend-button" @disabled($resendWaitSeconds > 0)
                    data-wait="{{ $resendWaitSeconds }}">
                {{ $resendWaitSeconds > 0 ? "Send a new code ({$resendWaitSeconds}s)" : 'Send a new code' }}
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="gg-button gg-button-surface" type="submit">Sign out</button>
        </form>
    </div>
</main>
<script>
    (function () {
        var button = document.getElementById('resend-button');
        var wait = button ? parseInt(button.dataset.wait, 10) : 0;
        if (!button || !wait) { return; }
        var timer = setInterval(function () {
            wait -= 1;
            if (wait <= 0) {
                clearInterval(timer);
                button.disabled = false;
                button.textContent = 'Send a new code';
            } else {
                button.textContent = 'Send a new code (' + wait + 's)';
            }
        }, 1000);
    })();
</script>
@endsection
