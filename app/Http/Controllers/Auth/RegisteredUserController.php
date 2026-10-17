<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\ActivityLog;
use App\Notifications\UserRegisteredNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'graduationYears' => range(date('Y') - 10, date('Y') + 5),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'graduation_year' => ['required', 'digits:4', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'program' => ['required', 'string', 'max:255'],
        ]);

        // Check if first user (bootstrap admin)
        $isFirstUser = User::count() === 0;

        // Generate Student ID
        $studentId = $isFirstUser
            ? 'ADMIN001'
            : 'STU' . date('Y') . mt_rand(1000, 9999);

        // SECURITY: is_approved is NOT fillable on the User model.
        // Every user starts unapproved. The bootstrap admin is approved
        // explicitly below via approve() (which uses forceFill).
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'student_id' => $studentId,
            'graduation_year' => $request->graduation_year,
            'program' => $request->program,
        ]);

        // Assign role
        if ($isFirstUser) {
            $user->approve(); // first user is the bootstrap admin: approved immediately
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $user->assignRole($adminRole);
            $roleName = 'admin';
        } else {
            $roleName = ($request->graduation_year <= date('Y')) ? 'alumni' : 'student';
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($role);
        }

        // Create empty profile safely
        try {
            $user->profile()->create([
                'phone' => null,
                'address' => null,
                'current_job' => null,
                'company' => null,
                'bio' => null,
                'social_links' => [],
                'skills' => [],
                'interests' => [],
                'profile_completion' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create profile for user: ' . $user->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Log registration
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'register',
            'description' => 'User registered with role: ' . $roleName,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        // Notify ALL admins about the new registration (not just the first one)
        if (!$isFirstUser) {
            $admins = User::role('admin')->get();
            Notification::send($admins, new UserRegisteredNotification($user));
        }

        event(new Registered($user));

        Auth::login($user);

        // Send the user to the right place based on approval state
        if (!$user->is_approved) {
            return redirect()->route('pending-approval')
                ->with('success', 'Registration successful! Your account is pending admin approval.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Admin account created. You have full access.');
    }
}