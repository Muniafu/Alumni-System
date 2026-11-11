<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- FOUC guard: must be the FIRST thing in head, BEFORE any stylesheet --}}
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <title>{{ config('app.name', 'Laravel') }} — Alumni Network</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons (toggle icon) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Design tokens (AFTER Bootstrap) --}}
    <link href="/css/theme.css" rel="stylesheet">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(
                135deg,
                var(--surface-page),
                color-mix(in srgb, var(--brand-soft) 40%, var(--surface-page))
            );
            color: var(--text-strong);
        }
        .glass-card {
            background: color-mix(in srgb, var(--surface-card) 78%, transparent);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            border-top: 0.3rem solid var(--brand);
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.3s;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.15);
        }
        .cta-btn {
            border-radius: 2rem;
            font-weight: 600;
            font-size: 1.15rem;
            padding: 0.75rem 2rem;
        }
    </style>
</head>
<body>

    <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center text-center py-5">

        {{-- Branding --}}
        <div class="mb-5">
            <x-application-logo class="rounded-circle shadow-sm mb-3" style="width:80px; height:80px;" />
            <h1 class="display-4 fw-bold mb-2" style="color: var(--brand);"> Alumni Network</h1>
            <p class="lead mx-auto" style="max-width:600px; color: var(--text-muted);">
                A global community where connections turn into opportunities —
                <span style="color: var(--status-success); font-weight: 600;">learn, grow, and thrive together.</span>
            </p>
        </div>

        {{-- CTA Buttons --}}
        <div class="d-flex flex-column flex-md-row gap-3 mb-5">
            <a href="{{ route('login') }}" class="btn btn-primary cta-btn shadow">
                Login
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary cta-btn shadow">
                Register
            </a>
        </div>

        {{-- Feature Highlights --}}
        <div class="row text-center g-4 w-100 justify-content-center">
            <div class="col-md-4">
                <div class="glass-card p-4 h-100" style="border-top-color: var(--status-info);">
                    <h3 class="mb-3" style="color: var(--status-info);"> Alumni Events</h3>
                    <p class="mb-0" style="color: var(--text-muted);">
                        Stay connected through reunions, workshops, and networking meetups designed to
                        <span style="color: var(--status-success); font-weight: 500;">foster growth and belonging.</span>
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card p-4 h-100" style="border-top-color: var(--status-success);">
                    <h3 class="mb-3" style="color: var(--status-success);"> Job Opportunities</h3>
                    <p class="mb-0" style="color: var(--text-muted);">
                        Explore global career opportunities and alumni-driven postings that empower
                        <span style="color: var(--status-warning); font-weight: 500;">your professional journey.</span>
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card p-4 h-100" style="border-top-color: var(--status-warning);">
                    <h3 class="mb-3" style="color: var(--status-warning);"> Mentorship</h3>
                    <p class="mb-0" style="color: var(--text-muted);">
                        Find or become a mentor to inspire students and alumni alike,
                        strengthening our <span style="color: var(--brand); font-weight: 500;">circle of support.</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <footer class="mt-5 small text-center" style="color: var(--text-muted);">
            <p>&copy; {{ date('Y') }} Alumni System. All rights reserved.</p>
            <p>
                <a href="#" class="text-decoration-underline" style="color: var(--brand);">Accessibility</a> •
                <a href="#" class="text-decoration-underline" style="color: var(--brand);">Privacy Policy</a>
            </p>
        </footer>
    </div>

    {{-- Theme toggle --}}
    <button id="themeToggle" class="theme-toggle-btn" type="button" aria-label="Toggle dark mode" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
    </button>
    <script src="/js/theme-toggle.js"></script>

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
