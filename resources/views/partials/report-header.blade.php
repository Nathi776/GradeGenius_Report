<header class="gg-header">
    <a href="{{ route('dashboard') }}" class="gg-brand">
        <span class="gg-brand-mark">@include('partials.icon', ['name' => 'graduation-cap'])</span>
        <span>GradeGenius</span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="gg-button gg-button-surface" type="submit">Sign out</button>
    </form>
</header>
