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

    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Design tokens (must load AFTER Bootstrap) -->
    <link href="/css/theme.css" rel="stylesheet">

    <!-- Bootstrap Icons (theme toggle uses bi-moon-stars / bi-sun) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Expose Laravel data to JS -->
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            @auth
                userId: '{{ auth()->id() }}',
            @else
                userId: null,
            @endauth
        };
    </script>

    <!-- Notification Sound -->
    <audio id="notificationSound" src="{{ asset('sounds/notification.mp3') }}" preload="auto"></audio>
</head>

<body class="bg-light">
    <div id="app">
        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (trim($__env->yieldContent('header')))
            <header class="bg-white border-bottom shadow-sm">
                <div class="container py-3">
                    @yield('header')
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="container my-4">
            @yield('content')
        </main>
    </div>

    {{-- Theme toggle (matches welcome/dashboard) --}}
    <button id="themeToggle" class="theme-toggle-btn" type="button" aria-label="Toggle dark mode" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
    </button>
    <script src="/js/theme-toggle.js"></script>
    <script src="/js/password-toggle.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- The old inline mark-as-read script lived here. DELETED: app.js
         already handles .mark-as-read clicks with one delegated listener —
         keeping both fired TWO requests per click and decremented the
         unread badge twice. --}}

    @stack('scripts')
</body>
</html>
