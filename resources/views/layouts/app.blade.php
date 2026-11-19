<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', isset($title) ? $title . ' | GradeGenius Reports' : 'GradeGenius Reports | Turn Grades Into Greatness')@if (isset($selectedTerm, $terms)) — {{ $selectedTerm === 'all' ? 'All Terms' : ($terms[(int) $selectedTerm] ?? '') }}@endif</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Poppins:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <script>
        (function() {
            const saved = localStorage.getItem('grade_genius_theme');
            const theme = saved ? saved : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="gg-page">
    @yield('content')
</body>
</html>

