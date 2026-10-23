<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ProfileDetailsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the logged-in user's profile (read-only view).
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $profile = $user->profile;

        return view('profile.show', compact('user', 'profile'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $profile = $user->profile()->firstOrCreate([], [
            'phone' => null,
            'address' => null,
            'current_job' => null,
            'company' => null,
            'bio' => null,
            'skills' => '',
            'interests' => '',
            'social_links' => '',
            'profile_completion' => 0,
        ]);

        $profile->calculateCompletion();

        return view('profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the user's basic profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's extended profile details.
     */
    public function updateDetails(ProfileDetailsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Convert comma-separated inputs to array for mutators
        $data['skills'] = isset($data['skills']) ? array_map('trim', explode(',', $data['skills'])) : [];
        $data['interests'] = isset($data['interests']) ? array_map('trim', explode(',', $data['interests'])) : [];
        $data['certifications'] = isset($data['certifications']) ? array_map('trim', explode(',', $data['certifications'])) : [];

        // Social links array
        $socialLinks = [
            $data['linkedin'] ?? null,
            $data['twitter'] ?? null,
            $data['github'] ?? null,
            $data['website'] ?? null,
        ];
        $data['social_links'] = array_filter($socialLinks);

        unset($data['linkedin'], $data['twitter'], $data['github'], $data['website']);

        $profile = $user->profile()->firstOrCreate([]);
        $profile->fill($data)->save();

        $profile->calculateCompletion();

        return redirect()->route('profile.edit')->with('status', 'profile-details-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete BEFORE logging out — UserObserver logs the deletion with
        // auth()->id(), which is null after logout (NOT NULL violation = the 500).
        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
