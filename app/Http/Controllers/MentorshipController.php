<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MentorProfile;
use App\Models\Mentorship;
use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\MentorshipRequestNotification;
use App\Notifications\MentorshipAcceptedNotification;
use App\Notifications\MentorshipStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class MentorshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mentorships = Mentorship::with(['mentor', 'mentee'])
            ->where(function ($query) {
                $query->where('mentor_id', Auth::id())
                    ->orWhere('mentee_id', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return view('mentorship.index', compact('mentorships'));
    }

    /**
     * Mentor signup form (alumni with create-mentorship permission).
     */
    public function create()
    {
        $mentorProfile = Auth::user()->mentorProfile;

        return view('mentorship.create', compact('mentorProfile'));
    }

    /**
     * Store (or update) the alumni's mentor profile.
     * Previously this created a broken mentee-less Mentorship row —
     * a Mentorship is a relationship, not a signup.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expertise' => 'required|string|max:255',
            'bio' => 'required|string|max:1000',
        ]);

        MentorProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'expertise' => $request->expertise,
                'bio' => $request->bio,
            ]
        );

        return redirect()->route('mentorship.find')
            ->with('success', 'You have successfully signed up as a mentor!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mentorship $mentorship)
    {
        $this->authorize('view', $mentorship);

        $mentorship->load(['mentor.profile', 'mentee.profile']);
        return view('mentorship.show', compact('mentorship'));
    }

    public function showMentor(User $mentor)
    {
        $mentor->load(['profile', 'mentorProfile']);

        // Only pending/accepted requests block a new one — a rejected
        // request no longer blocks re-requesting.
        $existingRequest = MentorshipRequest::where('mentor_id', $mentor->id)
            ->where('mentee_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted'])
            ->latest()
            ->first();

        return view('mentorship.show-mentor', compact('mentor', 'existingRequest'));
    }

    public function findMentors()
    {
        // Only alumni who explicitly signed up as mentors, and who have
        // a profile (the cards render profile fields).
        $mentors = User::role('alumni')
            ->whereHas('mentorProfile')
            ->whereHas('profile')
            ->with(['profile', 'mentorProfile'])
            ->paginate(15);

        return view('mentorship.find-mentors', compact('mentors'));
    }

    public function sendRequest(Request $request, User $mentor)
    {
        $request->validate([
            'message' => 'required|string|min:10|max:1000',
            'goal' => 'required|string|min:10|max:500',
        ]);

        if ($mentor->id === Auth::id() || ! $mentor->hasRole('alumni')) {
            return back()->with('error', 'You can only request mentorship from alumni mentors.');
        }

        $existingRequest = MentorshipRequest::where('mentor_id', $mentor->id)
            ->where('mentee_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a ' . $existingRequest->status . ' request with this mentor.');
        }

        $existingMentorship = Mentorship::where('mentor_id', $mentor->id)
            ->where('mentee_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if ($existingMentorship) {
            return back()->with('error', 'You already have an active mentorship with this mentor.');
        }

        $mentorshipRequest = MentorshipRequest::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => Auth::id(),
            'message' => $request->message,
            'goal' => $request->goal,
            'status' => 'pending',
        ]);

        // Notify mentor + admins
        $mentor->notify(new MentorshipRequestNotification($mentorshipRequest));
        Notification::send(User::role('admin')->get(), new MentorshipRequestNotification($mentorshipRequest));

        return redirect()->route('mentorship.requests')
            ->with('success', 'Mentorship request sent successfully');
    }

    public function myRequests()
    {
        $receivedRequests = MentorshipRequest::with(['mentee', 'mentor'])
            ->where('mentor_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'received_page');

        $sentRequests = MentorshipRequest::with(['mentee', 'mentor'])
            ->where('mentee_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'sent_page');

        return view('mentorship.requests', compact('receivedRequests', 'sentRequests'));
    }

    public function respondToRequest(Request $request, MentorshipRequest $mentorshipRequest)
    {
        $this->authorize('respond', $mentorshipRequest);

        $request->validate([
            'response' => 'required|in:accept,reject',
            'message' => 'nullable|string|max:500',
        ]);

        if ($request->response === 'accept') {
            $mentorship = DB::transaction(function () use ($mentorshipRequest) {
                $mentorshipRequest->update(['status' => 'accepted']);

                return Mentorship::create([
                    'mentor_id' => $mentorshipRequest->mentor_id,
                    'mentee_id' => $mentorshipRequest->mentee_id,
                    'mentorship_request_id' => $mentorshipRequest->id,
                    'goal' => $mentorshipRequest->goal,
                    'start_date' => now(),
                    'status' => 'active',
                ]);
            });

            // Notify mentee + admins (Mentorship::create does not trigger
            // the model's "updated" hook, so no duplication here)
            $mentorshipRequest->mentee?->notify(new MentorshipAcceptedNotification($mentorship));
            Notification::send(User::role('admin')->get(), new MentorshipAcceptedNotification($mentorship));

            return back()->with('success', 'Mentorship request accepted.');
        }

        // Rejection keeps the mentee's original message intact
        $mentorshipRequest->update([
            'status' => 'rejected',
            'response_message' => $request->message ?? 'Request declined',
        ]);

        $mentorshipRequest->mentee?->notify(
            new MentorshipStatusChangedNotification($mentorshipRequest, 'rejected')
        );
        Notification::send(
            User::role('admin')->get(),
            new MentorshipStatusChangedNotification($mentorshipRequest, 'rejected')
        );

        return back()->with('success', 'Mentorship request declined.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mentorship $mentorship)
    {
        $this->authorize('update', $mentorship);

        $request->validate([
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $mentorship->update([
            'status' => $request->status,
            'end_date' => in_array($request->status, ['completed', 'cancelled']) ? now() : null,
            'notes' => $request->notes,
        ]);

        // Status-change notifications are sent once by the Mentorship
        // model's "updated" hook — do not duplicate them here.

        return back()->with('success', 'Mentorship updated successfully');
    }
}
