<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Alumni System</title>

    {{-- FOUC guard: must be the FIRST thing in head, BEFORE any stylesheet --}}
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

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(
                to bottom right,
                color-mix(in srgb, var(--brand) 14%, var(--surface-page)),
                var(--surface-page)
            );
            color: var(--text-strong);
        }
        .card-glass {
            background: color-mix(in srgb, var(--surface-card) 88%, transparent);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            border-top: 0.3rem solid var(--brand);
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.3s;
        }
        .card-glass:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.15);
        }
        .btn-brand {
            background-color: var(--brand);
            border-color: var(--brand);
            color: #fff;
            font-weight: 600;
        }
        .btn-brand:hover {
            background-color: color-mix(in srgb, var(--brand) 85%, #000);
            border-color: color-mix(in srgb, var(--brand) 85%, #000);
            color: #fff;
        }
        .header-title {
            color: var(--brand);
        }
        a {
            text-decoration: none;
        }
    </style>
</head>
<body class="auth-body">

    <div class="d-flex flex-column justify-content-center align-items-center min-vh-100 px-3">

        <!-- Branding -->
        <div class="text-center mb-5">
            <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto;">
                <x-application-logo class="w-100 h-100" />
            </div>
            <h1 class="display-5 fw-bold header-title">Welcome Back</h1>
            <p class="text-secondary">Log in to continue your alumni journey</p>
        </div>

        <!-- Auth Card -->
        <div class="card card-glass p-4 p-md-5 shadow-lg" style="max-width: 400px; width: 100%;">
            <div class="card-body">

                <!-- Session Status -->
                <x-auth-session-status class="mb-3" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email" class="form-control rounded-pill shadow-sm"
                               value="{{ old('email') }}" required autofocus autocomplete="username">
                        <x-input-error :messages="$errors->get('email')" class="text-danger mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="password-wrap">
                            <input type="password" name="password" id="password" class="form-control rounded-pill shadow-sm"
                                required autocomplete="current-password">
                            <button type="button" class="password-eye" data-toggle-password for="password" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="text-danger mt-1" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>

                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small text-primary">Forgot password?</a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-brand w-100 py-2 rounded-pill shadow">Log In</button>
                </form>

                <!-- Register Redirect -->
                <div class="mt-4 text-center">
                    <p class="small text-secondary">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="fw-semibold text-primary">Register</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-5 text-center text-secondary small">
            &copy; {{ date('Y') }} Alumni System. All rights reserved.
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
