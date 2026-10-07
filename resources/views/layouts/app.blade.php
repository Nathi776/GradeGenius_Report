<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @hasSection('title')
            @yield('title') · GradeGenius Reports
        @else
            GradeGenius Reports
        @endif
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">

    <div class="min-h-screen lg:flex">

        {{-- Sidebar --}}
        <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">

            {{-- Brand --}}
            <div class="flex h-20 items-center border-b border-slate-200 px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">
                        GG
                    </div>

                    <div>
                        <div class="text-sm font-bold text-slate-900">
                            GradeGenius
                        </div>

                        <div class="text-xs text-slate-500">
                            Reports
                        </div>
                    </div>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 px-4 py-6">

                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Overview
                </p>

                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-lg bg-indigo-50 px-3 py-2.5 text-sm font-medium text-indigo-700"
                >
                    <span>▦</span>
                    Dashboard
                </a>

                <p class="mb-3 mt-8 px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Reports
                </p>

                <a
                    href="{{ route('student.reports') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                >
                    <span>◫</span>
                    Student Reports
                </a>

                <a
                    href="{{ route('parent.reports') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                >
                    <span>◉</span>
                    Parent Reports
                </a>

                <a
                    href="{{ route('teacher.reports') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                >
                    <span>▤</span>
                    Teacher Reports
                </a>

            </nav>

            {{-- User --}}
            <div class="border-t border-slate-200 p-4">
                <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        N
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">
                            Demo User
                        </p>

                        <p class="text-xs text-slate-500">
                            Student
                        </p>
                    </div>
                </div>
            </div>

        </aside>

        {{-- Main area --}}
        <div class="min-w-0 flex-1">

            {{-- Topbar --}}
            <header class="sticky top-0 z-10 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">

                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
                        GradeGenius
                    </p>

                    <h1 class="text-lg font-semibold text-slate-900">
                        @yield('page-heading', 'Reports Dashboard')
                    </h1>
                </div>

                <div class="flex items-center gap-3">

                    <button
                        type="button"
                        class="hidden rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 sm:block"
                    >
                        Notifications
                    </button>

                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                        N
                    </div>

                </div>

            </header>

            {{-- Page content --}}
            <main class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

        </div>

    </div>

</body>
</html>