<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EventRsvp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Notifications\EventCreatedNotification;
use App\Notifications\EventRsvpNotification;

class EventController extends Controller
{
    public function index()
    {
        // Eager load organizer and attendee counts to avoid N+1
        $events = Event::with('organizer')
            ->withCount(['attendees as attendees_going_count' => fn($q) => $q->where('event_rsvps.status', 'going')])
            ->upcoming()
            ->orderBy('start')
            ->paginate(10);

        return view('events.index', compact('events'));
    }

    /**
     * Guest-safe public listing for /public/events.
     * Upcoming events only; never exposes meeting_url, RSVP notes
     * or attendee details to guests.
     */
    public function publicIndex()
    {
        $events = Event::query()
            ->with('organizer:id,name')
            ->upcoming()
            ->orderBy('start')
            ->paginate(10);

        return view('events.public', compact('events'));
    }

    public function calendar()
    {
        return view('events.calendar');
    }

    public function getCalendarEvents(Request $request)
    {
        $validated = $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        [$start, $end] = [$validated['start'], $validated['end']];

        // Include events that start or end inside the window, AND events
        // that span the entire window (start before it, end after it).
        $events = Event::where(function ($q) use ($start, $end) {
                $q->whereBetween('start', [$start, $end])
                  ->orWhereBetween('end', [$start, $end])
                  ->orWhere(fn($q2) => $q2->where('start', '<', $start)
                                           ->where('end', '>', $end));
            })
            ->get()
            ->map(fn($event) => [
                'id'       => $event->id,
                'title'    => $event->title,
                'start'    => $event->start->toIso8601String(),
                'end'      => $event->end->toIso8601String(),
                'location' => $event->location,
                'url'      => route('events.show', $event->id),
                'color'    => $event->is_online ? '#3b82f6' : '#10b981',
            ]);

        return response()->json($events);
    }

    public function create()
    {
        Gate::authorize('create', Event::class);
        return view('events.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Event::class);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start'       => 'required|date',
            'end'         => 'required|date|after:start',
            'location'    => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
            'capacity'    => 'nullable|integer|min:1',
            'is_online'   => 'boolean',
            // A checked checkbox posts "1" (not "true"), so required_if
            // previously never fired. Use Rule::requiredIf with a boolean.
            'meeting_url' => ['nullable', 'url', Rule::requiredIf($request->boolean('is_online'))],
        ]);

        // Normalize checkbox state so an unchecked box actually clears the flag.
        $validated['is_online']   = $request->boolean('is_online');
        $validated['meeting_url'] = $validated['is_online'] ? ($validated['meeting_url'] ?? null) : null;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $validated['user_id'] = auth()->id();
        $event = Event::create($validated);

        // Notify approved students/alumni (queued notification)
        $recipients = User::query()
            ->where('id', '!=', auth()->id())
            ->where('is_approved', true)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['student', 'alumni']))
            ->get();

        Notification::send($recipients, new EventCreatedNotification($event));

        return redirect()->route('events.show', $event)
                         ->with('success', 'Event created successfully');
    }

    public function show(Event $event)
    {
        $userRsvp  = $event->rsvps()->where('user_id', auth()->id())->first();
        $attendees = $event->rsvps()->with('user')->where('status', 'going')->get();

        return view('events.show', compact('event', 'userRsvp', 'attendees'));
    }

    public function edit(Event $event)
    {
        Gate::authorize('update', $event);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start'       => 'required|date',
            'end'         => 'required|date|after:start',
            'location'    => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
            'capacity'    => 'nullable|integer|min:1',
            'is_online'   => 'boolean',
            'meeting_url' => ['nullable', 'url', Rule::requiredIf($request->boolean('is_online'))],
        ]);

        // Without this, an unchecked is_online never reverted the flag on edit.
        $validated['is_online']   = $request->boolean('is_online');
        $validated['meeting_url'] = $validated['is_online'] ? ($validated['meeting_url'] ?? null) : null;

        if ($request->hasFile('image')) {
            if ($event->image) Storage::disk('public')->delete($event->image);
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)
                         ->with('success', 'Event updated successfully');
    }

    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);

        // Soft delete — keep the stored image so a restored event stays intact.
        $event->delete();

        return redirect()->route('events.index')
                         ->with('success', 'Event deleted successfully');
    }

    /** RSVP Handling **/
    public function rsvp(Request $request, Event $event)
    {
        $this->authorize('rsvp', $event);

        $validated = $request->validate([
            'status' => 'required|in:going,interested,not_going',
            'guests' => 'nullable|integer|min:0',
            'notes'  => 'nullable|string|max:500',
        ]);

        // The capacity check and the write must happen atomically.
        // Previously two concurrent "going" RSVPs could both pass
        // isFull() and oversell the event. lockForUpdate() on the
        // event row serializes competing RSVPs.
        $result = DB::transaction(function () use ($event, $validated) {
            $locked = Event::whereKey($event->getKey())->lockForUpdate()->firstOrFail();

            // Users already marked "going" may still update their RSVP
            // (guests/notes) even if the event has filled up since.
            $alreadyGoing = $locked->rsvps()
                ->where('user_id', auth()->id())
                ->where('status', 'going')
                ->exists();

            if ($validated['status'] === 'going' && !$alreadyGoing && $locked->isFull()) {
                return ['full' => true];
            }

            try {
                $rsvp = $locked->rsvps()->updateOrCreate(
                    ['user_id' => auth()->id()],
                    $validated
                );
            } catch (QueryException $e) {
                // Double-submit race caught by the unique(event_id, user_id) index.
                if (($e->errorInfo[1] ?? null) !== 1062) {
                    throw $e;
                }
                return ['duplicate' => true];
            }

            return ['rsvp' => $rsvp];
        });

        if (isset($result['full'])) {
            return back()->withErrors(['status' => 'This event is full. You cannot RSVP as "Going".']);
        }
        if (isset($result['duplicate'])) {
            return back()->withErrors(['status' => 'You have already RSVPed to this event.']);
        }

        $rsvp = $result['rsvp'];

        // Notify organizer if not self
        if ($event->organizer && $event->organizer->id != auth()->id()) {
            $event->organizer->notify(new EventRsvpNotification($rsvp));
        }

        return back()->with('success', 'RSVP status updated');
    }

    public function cancelRsvp(Event $event)
    {
        $event->rsvps()->where('user_id', auth()->id())->delete();
        return back()->with('success', 'RSVP cancelled');
    }
}
