<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Clear any stale intended URL from previous user session to prevent 403 mismatches
        $request->session()->forget('url.intended');

        $user = Auth::user();

        // Unapproved users land on the friendly pending page insted of aloop
        if (!$user->is_approved) {
            return redirect()->route('pending-approval')
                ->with('info', 'Your account is pending admin approval.');
        }

        // Role-based redirection
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard'); //Adjust path if your route is named differently
        }

        if ($user->hasRole('alumni')) {
            return redirect()->route('alumni.dashboard');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
