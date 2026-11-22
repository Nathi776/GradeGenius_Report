@extends('layouts.app')

{{--
    Parent workspace shell: persistent left sidebar (collapses behind a menu button below lg) around
    whatever the page puts in the `parent-content` section. The sidebar carries the child switcher so a
    parent can change child without signing out. Items without an `href` are planned pages; they render
    as disabled "Soon" entries, never dead links.
--}}
@section('content')
    @php
        $active = $active ?? 'home';
        
        // Resolve verified children for the parent workspace
        $sidebarChildren = [];
        if (!empty($dashboard['children'])) {
            $sidebarChildren = $dashboard['children'];
            $activeChild = $dashboard['active_child'] ?? array_key_first($sidebarChildren);
        } elseif (isset($children) && $children instanceof \Illuminate\Support\Collection && $children->isNotEmpty()) {
            foreach ($children as $c) {
                $sidebarChildren[(string) $c->id] = $c->name;
            }
            $activeChild = isset($selectedChild) && $selectedChild ? (string) $selectedChild->id : (string) array_key_first($sidebarChildren);
        } else {
            $dbChildren = auth()->user()?->linkedChildren()->wherePivot('status', 'verified')->get();
            if ($dbChildren && $dbChildren->isNotEmpty()) {
                foreach ($dbChildren as $c) {
                    $sidebarChildren[(string) $c->id] = $c->name;
                }
                $activeChild = (string) (request('child_id') ?? request('child') ?? array_key_first($sidebarChildren));
            }
        }
        $activeChild = (string) ($activeChild ?? array_key_first($sidebarChildren));

        $childRoute = fn ($key) => match ($active) {
            'planner' => route('parent.planner', ['child_id' => $key]),
            'recap' => route('parent.planner.recap', ['child_id' => $key]),
            'progress' => route('parent.report', ['child' => $key]),
            default => route('parent.home', ['child' => $key]),
        };

        $groups = [
            'Overview' => [
                ['key' => 'home', 'label' => 'Dashboard', 'icon' => 'home', 'href' => $activeChild ? route('parent.home', ['child' => $activeChild]) : route('parent.home')],
                ['key' => 'progress', 'label' => 'Academic Progress', 'icon' => 'bar-chart', 'href' => $activeChild ? route('parent.report', ['child' => $activeChild]) : route('parent.reports')],
                ['key' => 'support', 'label' => 'Learning Support', 'icon' => 'sparkles', 'href' => ($activeChild ? route('parent.home', ['child' => $activeChild]) : route('parent.home')).'#support-heading'],
            ],
            'Plan ahead' => [
                ['key' => 'assessments', 'label' => 'Upcoming Assessments', 'icon' => 'calendar', 'href' => null],
                ['key' => 'planner', 'label' => 'Study Planner', 'icon' => 'check-circle', 'href' => $activeChild ? route('parent.planner', ['child_id' => $activeChild]) : route('parent.planner')],
            ],
            'Account' => [
                ['key' => 'notifications', 'label' => 'Notifications', 'icon' => 'mail', 'href' => null],
                ['key' => 'settings', 'label' => 'Settings', 'icon' => 'sliders', 'href' => null],
            ],
        ];
        $itemBase = 'flex items-center gap-3 rounded-xl border px-3 py-2 text-sm font-medium';
    @endphp

    <div class="portal-workspace mx-auto flex w-full max-w-360 flex-col gap-4 p-4 lg:flex-row lg:items-start lg:gap-6 lg:p-6">
        <aside class="no-print lg:sticky lg:top-6 lg:w-72 lg:shrink-0" aria-label="Parent workspace">
            <div class="flex flex-col rounded-2xl border border-line bg-card p-4 lg:max-h-[calc(100vh-3rem)]">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('parent.home') }}" class="gg-brand brand-wrapper">
                            <div class="app-icon-badge">
                                <img src="{{ asset('images/gradegenius_logo.png') }}" alt="GradeGenius Logo" class="logo-icon">
                            </div>
                            <div class="logo-text">Grade<span>Genius</span></div>
                        </a>
                        <p class="mt-1 text-xs text-muted">Parent workspace</p>
                    </div>
                    <button type="button" data-nav-toggle aria-expanded="false" aria-controls="parent-nav" aria-label="Toggle navigation menu"
                        class="inline-flex size-10 items-center justify-center rounded-xl border border-line bg-card-2 lg:hidden focus-visible:outline-2 focus-visible:outline-blue">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                </div>

                <div id="parent-nav" data-nav class="mt-4 flex min-h-0 flex-1 flex-col max-lg:hidden">
                    <nav aria-label="Parent navigation" class="min-h-0 flex-1 space-y-5 overflow-y-auto pr-1">
                        @if (count($sidebarChildren) > 0)
                            <div>
                                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-muted" id="children-label">Your children</p>
                                <ul class="mt-2 space-y-1" aria-labelledby="children-label">
                                    @foreach ($sidebarChildren as $key => $name)
                                        <li>
                                            <a href="{{ $childRoute($key) }}" @if ((string) $key === (string) $activeChild) aria-current="true" @endif
                                                class="{{ $itemBase }} focus-visible:outline-2 focus-visible:outline-blue {{ (string) $key === (string) $activeChild ? 'border-brand/40 bg-brand/15 text-ink' : 'border-transparent text-muted hover:bg-card-2 hover:text-ink' }}">
                                                @include('partials.icon', ['name' => 'users', 'size' => 18])
                                                <span class="min-w-0 flex-1 leading-snug">{{ $name }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @foreach ($groups as $heading => $items)
                            <div>
                                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-muted">{{ $heading }}</p>
                                <ul class="mt-2 space-y-1">
                                    @foreach ($items as $item)
                                        <li>
                                            @if ($item['href'])
                                                <a href="{{ $item['href'] }}" @if ($item['key'] === $active) aria-current="page" @endif
                                                    class="{{ $itemBase }} focus-visible:outline-2 focus-visible:outline-blue {{ $item['key'] === $active ? 'border-brand/40 bg-brand/15 text-ink' : 'border-transparent text-muted hover:bg-card-2 hover:text-ink' }}">
                                                    @include('partials.icon', ['name' => $item['icon'], 'size' => 18])
                                                    <span class="min-w-0 flex-1 leading-snug">{{ $item['label'] }}</span>
                                                </a>
                                            @else
                                                <span aria-disabled="true" class="{{ $itemBase }} cursor-not-allowed border-transparent text-muted">
                                                    @include('partials.icon', ['name' => $item['icon'], 'size' => 18])
                                                    <span class="min-w-0 flex-1 leading-snug">{{ $item['label'] }}</span>
                                                    <span class="shrink-0 rounded-full border border-line px-2 py-0.5 text-[10px] uppercase tracking-wider">Roadmap</span>
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </nav>

                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-line pt-4">
                        @include('partials.theme-toggle')
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="gg-button gg-button-surface" type="submit">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            @include('partials.workspace-header')
            @yield('parent-content')
        </main>
    </div>
@endsection
