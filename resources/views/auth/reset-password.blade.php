<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Alumni System</title>
    
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
        <h1 class="h4 fw-bold header-title">Reset Your Password</h1>
        <p class="text-muted mt-2" style="max-width: 400px; margin: 0 auto;">
            Enter a new password below to regain access to your account.
        </p>
    </div>

    <!-- Card -->
    <div class="card card-glass p-4 p-md-5 shadow-lg" style="max-width: 450px; width: 100%;">

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Hidden Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Input -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" id="email"
                       class="form-control rounded-pill shadow-sm"
                       required autofocus autocomplete="username"
                       placeholder="your@email.com"
                       value="{{ old('email', $request->email) }}">
                <x-input-error :messages="$errors->get('email')" class="text-danger mt-1" />
            </div>

            <!-- Password Input -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">New Password</label>
                <input type="password" name="password" id="password"
                       class="form-control rounded-pill shadow-sm"
                       required autocomplete="new-password"
                       placeholder="Enter new password">
                <x-input-error :messages="$errors->get('password')" class="text-danger mt-1" />
            </div>

            <!-- Confirm Password Input -->
            <div class="mb-3">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control rounded-pill shadow-sm"
                       required autocomplete="new-password"
                       placeholder="Confirm new password">
                <x-input-error :messages="$errors->get('password_confirmation')" class="text-danger mt-1" />
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-brand rounded-pill px-4 py-2 shadow">
                    Reset Password
                </button>
            </div>
        </form>

    </div>

    <!-- Footer -->
    <footer class="mt-5 text-center text-secondary small">
        &copy; {{ date('Y') }} Alumni System · Secure your account.
    </footer>

</div>
    
    {{-- Theme toggle --}}
    <button id="themeToggle" class="theme-toggle-btn" type="button" aria-label="Toggle dark mode" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
    </button>
    
    <script src="/js/theme-toggle.js"></script>
    <script src="/js/password-toggle.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
