<header class="gg-header">
    <a href="{{ route('dashboard') }}" class="gg-brand brand-wrapper">
        <div class="app-icon-badge">
            <img src="{{ asset('images/gradegenius_logo.png') }}" alt="GradeGenius Logo" class="logo-icon">
        </div>
        <div class="logo-text">Grade<span>Genius</span></div>
        <span class="logo-reports-badge">Reports</span>
    </a>
    <div class="gg-header-actions">
        <a href="{{ route('dashboard.landing') }}" class="gg-nav-link">Overview</a>
        @include('partials.theme-toggle')
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="gg-button gg-button-surface" type="submit">Sign out</button>
        </form>
    </div>
</header>

