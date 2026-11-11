<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- FOUC guard: must be the FIRST thing in head, BEFORE any stylesheet --}}
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <title>Dashboard — {{ config('app.name', 'Laravel') }}</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
            background: var(--surface-page);
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
        .header-title { color: var(--brand); font-weight: 600; }
        .student-id-badge {
            background: var(--status-success-bg);
            color: var(--status-success);
            padding: 0.3rem 0.8rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <div class="container py-5">

        {{-- Header --}}
        <div class="mb-4 text-center">
            <h2 class="header-title display-6">Dashboard</h2>
        </div>

        {{-- Welcome Card --}}
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-glass p-4">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold mb-2">Welcome, {{ auth()->user()->name }}!</h5>

                        {{-- Student ID (only for users who actually have one) --}}
                        @if(auth()->user()->student_id)
                            <p class="mb-3">
                                Your Student ID:
                                <span class="student-id-badge">{{ auth()->user()->student_id }}</span>
                            </p>
                        @endif

                        <p class="card-text lead mb-4" style="color: var(--text-muted);">
                            You're successfully logged in to the Alumni System.
                        </p>

                        {{-- Feature Quick Links --}}
                        <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg shadow">Admin Dashboard</a>
                                <a href="{{ route('admin.user-management') }}" class="btn btn-outline-primary btn-lg shadow">User Management</a>
                            @elseif(auth()->user()->hasRole('alumni'))
                                <a href="{{ route('alumni.dashboard') }}" class="btn btn-success btn-lg shadow">Alumni Dashboard</a>
                                <a href="{{ route('alumni.profile') }}" class="btn btn-outline-success btn-lg shadow">My Profile</a>
                            @elseif(auth()->user()->hasRole('student'))
                                <a href="{{ route('student.dashboard') }}" class="btn btn-warning btn-lg shadow">Student Dashboard</a>
                                <a href="{{ route('student.profile') }}" class="btn btn-outline-warning btn-lg shadow">My Profile</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats Cards --}}
        <div class="row mt-5 g-4">
            <div class="col-md-4">
                <div class="card card-glass p-4 text-center" style="border-top-color: var(--status-info);">
                    <h5 class="fw-semibold" style="color: var(--status-info);">📅 Upcoming Events</h5>
                    <p class="mb-0" style="color: var(--text-muted);">Stay updated with the latest reunions and workshops to expand your network.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-glass p-4 text-center" style="border-top-color: var(--status-success);">
                    <h5 class="fw-semibold" style="color: var(--status-success);">💼 Job Opportunities</h5>
                    <p class="mb-0" style="color: var(--text-muted);">Access career opportunities and postings from alumni around the globe.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-glass p-4 text-center" style="border-top-color: var(--status-warning);">
                    <h5 class="fw-semibold" style="color: var(--status-warning);">🤝 Mentorship</h5>
                    <p class="mb-0" style="color: var(--text-muted);">Connect with mentors or support students to foster growth within the community.</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Theme toggle --}}
    <button id="themeToggle" class="theme-toggle-btn" type="button" aria-label="Toggle dark mode" title="Toggle theme">
        <i class="bi bi-moon-stars"></i>
    </button>
    <script src="/js/theme-toggle.js"></script>

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
