<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StudentController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | NOTE ON REMOVED METHODS
    |----------------------------------------------------------------------
    | rsvpEvent() and applyJob() were removed. They duplicated
    | EventController::rsvp() and JobPostingController::apply() with
    | bugs the canonical versions don't have:
    |
    | - rsvpEvent() used rsvps()->create(), which crashes with a SQL
    |   error when a user RSVPs twice (unique event_id+user_id index).
    |   EventController::rsvp() correctly uses updateOrCreate().
    | - applyJob() wrote a non-existent `resume` column (the table has
    |   `resume_path`), stored no uploaded file, had no validation and
    |   used an invalid `status => 'pending'` value ('submitted' is the
    |   first valid status).
    |
    | After pasting this file, run:
    |     grep -rn "rsvpEvent\|applyJob" routes/
    | If anything points at StudentController, repoint those routes to
    | EventController::rsvp and JobPostingController::apply.
    */

    public function dashboard()
    {
        Gate::authorize('access-student-dashboard');

        $user = Auth::user();
        $user->load('profile');

        $canViewEvents = $user->can('view-events');
        $canRSVPEvents = $user->can('rsvp-events');
        $upcomingEvents = [];

        if ($canViewEvents) {
            $upcomingEvents = Event::upcoming()
                ->orderBy('start')
                ->limit(5)
                ->get();
        }

        $recentJobs = JobPosting::active()
            ->latest()
            ->limit(5)
            ->get();

        return view('student.dashboard', [
            'user' => $user,
            'profile' => $user->profile,
            'canViewEvents' => $canViewEvents,
            'canRSVPEvents' => $canRSVPEvents,
            'upcomingEvents' => $upcomingEvents,
            'recentJobs' => $recentJobs,
        ]);
    }

    /**
     * Show the student profile edit page.
     */
    public function editProfile()
    {
        $user = Auth::user();
        Gate::authorize('edit-profile', $user);
        $this->ensureProfileExists($user);

        return view('student.profile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update the student profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        Gate::authorize('edit-profile', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'linkedin' => ['nullable', 'url'],
            'twitter' => ['nullable', 'url'],
        ]);

        $user->update(['name' => $validated['name']]);

        $user->profile()->updateOrCreate([], $this->prepareProfileData($validated));
        $user->profile->calculateCompletion();

        return redirect()->route('student.dashboard')
            ->with('success', 'Profile updated successfully');
    }

    /*
    |----------------------------------------------------------------------
    | Helpers
    |----------------------------------------------------------------------
    */

    protected function ensureProfileExists(User $user): void
    {
        if (!$user->profile) {
            $user->profile()->create([
                'phone' => null,
                'address' => null,
                'bio' => null,
                'social_links' => [],
                'profile_completion' => 0,
            ]);
        }
    }

    protected function prepareProfileData(array $data): array
    {
        return [
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'bio' => $data['bio'] ?? null,
            'social_links' => [
                'linkedin' => $data['linkedin'] ?? null,
                'twitter' => $data['twitter'] ?? null,
            ],
        ];
    }
}
