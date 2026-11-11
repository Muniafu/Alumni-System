<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Alumni System</title>
    
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
        <h1 class="display-5 fw-bold header-title">Join the Alumni Network</h1>
        <p class="text-secondary">Create your account to connect, grow, and thrive</p>
    </div>

    <!-- Register Card -->
    <div class="card card-glass p-4 p-md-5 shadow-lg" style="max-width: 450px; width: 100%;">
        <div class="card-body">

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" id="name" class="form-control rounded-pill shadow-sm"
                           value="{{ old('name') }}" required autofocus autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="text-danger mt-1" />
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" id="email" class="form-control rounded-pill shadow-sm"
                           value="{{ old('email') }}" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="text-danger mt-1" />
                </div>

                <!-- Graduation Year -->
                <div class="mb-3">
                    <label for="graduation_year" class="form-label fw-semibold">Graduation Year</label>
                    <select id="graduation_year" name="graduation_year" class="form-select rounded-pill shadow-sm" required>
                        <option value="">Select Graduation Year</option>
                        @foreach($graduationYears as $year)
                            <option value="{{ $year }}" {{ old('graduation_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('graduation_year')" class="text-danger mt-1" />
                </div>

                <!-- Program -->
                <div class="mb-3">
                    <label for="program" class="form-label fw-semibold">Program</label>
                    <input type="text" name="program" id="program" class="form-control rounded-pill shadow-sm"
                           value="{{ old('program') }}" required>
                    <x-input-error :messages="$errors->get('program')" class="text-danger mt-1" />
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

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                    <div class="password-wrap">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control rounded-pill shadow-sm" required autocomplete="new-password">
                        <button type="button" class="password-eye" data-toggle-password for="password_confirmation" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="text-danger mt-1" />
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('login') }}" class="small text-secondary">Already registered?</a>
                    <button type="submit" class="btn btn-brand rounded-pill px-4 py-2 shadow">Register</button>
                </div>
            </form>

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
