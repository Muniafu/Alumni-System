<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Approval - Alumni System</title>
    
        {{-- FOUC guard: set theme BEFORE first paint --}}
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (toggle icon) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Design tokens (AFTER Bootstrap) -->
    <link href="/css/theme.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">

<div class="d-flex flex-column justify-content-center align-items-center min-vh-100 px-3">

    <!-- Branding -->
    <div class="text-center mb-5">
        <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto;">
            <x-application-logo class="w-100 h-100" />
        </div>
        <h1 class="h4 fw-bold header-title">Account Pending Approval</h1>
    </div>

    <!-- Main Card -->
    <div class="card card-glass-warning p-4 p-md-5 shadow-lg text-center" style="max-width: 500px; width: 100%;">
        <div class="mb-4">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning shadow-sm icon-circle mb-3">
                ⏳
            </span>
            <h2 class="h5 fw-semibold text-secondary">Your Account is Under Review</h2>
            <p class="mt-3 text-muted" style="max-width: 400px; margin: 0 auto;">
                Thank you for registering with the <span class="text-primary fw-semibold">Alumni Network</span>.
                Our administrators are currently reviewing your account details.
                You’ll be notified by email once your access is approved ✅.
            </p>
        </div>

        <!-- Action Button -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-brand rounded-pill px-4 py-2 shadow">
                Log Out
            </button>
        </form>

        <p class="mt-4 text-muted small">You can log in again later once your account is approved.</p>
    </div>

    <!-- Footer -->
    <footer class="mt-5 text-center text-secondary small">
        &copy; {{ date('Y') }} Alumni System · Empowering lifelong connections.
    </footer>
</div>

    {{-- Theme toggle --}}
    <button id="themeToggle" class="theme-toggle-btn" type="button" aria-label="Toggle dark mode" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
    </button>
    
    <script src="/js/theme-toggle.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
