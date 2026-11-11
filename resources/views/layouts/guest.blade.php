<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- FOUC guard: set the theme BEFORE anything paints --}}
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Design tokens (must load AFTER Bootstrap) -->
    <link href="/css/theme.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light">

    <div class="d-flex flex-column min-vh-100 justify-content-center align-items-center py-4">

        <!-- Logo -->
        <div class="mb-4 text-center">
            <a href="/">
                <x-application-logo class="img-fluid" style="width: 80px; height: 80px;" />
            </a>
        </div>

        <!-- Card Wrapper for Form -->
        <div class="card shadow-sm w-100" style="max-width: 400px;">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>
    </div>

    {{-- Theme toggle (matches the app layout) --}}
    <button id="themeToggle" class="theme-toggle-btn" type="button" aria-label="Toggle dark mode" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
    </button>
    <script src="/js/theme-toggle.js"></script>

    <!-- Bootstrap Bundle JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
