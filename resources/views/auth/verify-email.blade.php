<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - Alumni System</title>
    
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
        <h1 class="h4 fw-bold header-title">Verify Your Email</h1>
        <p class="text-muted mt-2" style="max-width: 400px; margin: 0 auto;">
            ✉️ Thanks for joining the <span class="fw-semibold text-primary">Alumni Network</span>!
            Please confirm your email address to get started.
        </p>
    </div>

    <!-- Card -->
    <div class="card card-glass p-4 p-md-5 shadow-lg" style="max-width: 500px; width: 100%;">

        <p class="text-secondary mb-3 small">
            {{ __('Before continuing, check your inbox for the verification link. Didn’t get it? We can send another one.') }}
        </p>

        <!-- Success Message -->
        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success mb-3" role="alert">
                ✅ {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <!-- Actions -->
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mt-4">

            <!-- Resend Verification -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-brand btn-lg w-100">
                    {{ __('Resend Verification Email') }}
                </button>
            </form>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-lg w-100">
                    {{ __('Log Out') }}
                </button>
            </form>

        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 text-center text-secondary small">
        &copy; {{ date('Y') }} Alumni System · Stay connected, stay verified.
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
