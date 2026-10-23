@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">
            {{ __('Event Details') }}
        </h2>
        <div class="d-flex gap-2">
            @can('update', $event)
                <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
            @endcan
            @can('delete', $event)
                <form method="POST" action="{{ route('events.destroy', $event) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <!-- Event Details -->
        <div class="col-lg-8">
            @if($event->image)
                <img src="{{ Storage::url($event->image) }}"
                     class="img-fluid rounded shadow-sm mb-4"
                     alt="{{ $event->title }}">
            @endif

            <h1 class="fw-bold text-dark">{{ $event->title }}</h1>
            <p class="text-muted small">
                <i class="bi bi-person-circle text-secondary"></i>
                Organized by <strong>{{ $event->organizer->name }}</strong>
            </p>

            <div class="mt-4 bg-light p-4 rounded shadow-sm">
                <p class="mb-0 text-secondary" style="white-space: pre-line;">
                    {!! nl2br(e($event->description)) !!}
                </p>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- When -->
                    <h5 class="fw-semibold text-primary">
                        <i class="bi bi-calendar-event"></i> When
                    </h5>
                    <p class="text-muted">
                        {{ $event->start->format('l, F j, Y') }}<br>
                        {{ $event->start->format('g:i a') }} - {{ $event->end->format('g:i a') }}
                    </p>

                    <!-- Where -->
                    <h5 class="fw-semibold text-success mt-3">
                        <i class="bi bi-geo-alt-fill"></i> Where
                    </h5>
                    @if($event->is_online)
                        <p>
                            <span class="badge bg-info text-dark">Online Event</span><br>
                            {{-- Meeting link: organizer or attendees (going/interested) only --}}
                            @php
                                $canSeeMeetingUrl = auth()->check() && (
                                    auth()->id() === $event->user_id
                                    || ($userRsvp && in_array($userRsvp->status, ['going', 'interested']))
                                );
                            @endphp
                            @if($canSeeMeetingUrl)
                                <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener"
                                   class="link-primary fw-semibold">Join Meeting</a>
                            @else
                                <span class="text-muted small">
                                    <i class="bi bi-lock"></i>
                                    The meeting link becomes available once you RSVP as going or interested.
                                </span>
                            @endif
                        </p>
                    @elseif($event->location)
                        <p class="text-muted">{{ $event->location }}</p>
                    @endif

                    <!-- Attendees -->
                    <h5 class="fw-semibold text-warning mt-3">
                        <i class="bi bi-people-fill"></i> Attendees
                    </h5>
                    <p class="text-muted">
                        {{ $event->attendees_going }} going
                        @if($event->capacity)
                            ({{ $event->available_spots }} spots left)
                        @endif
                    </p>

                    <!-- RSVP / Alerts -->
                    <div class="mt-4">
                        @if($event->isFull())
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                This event is at capacity.
                            </div>
                        @elseif(!auth()->check())
                            {{-- Guests get a login prompt instead of a broken RSVP form --}}
                            <p class="text-muted small mb-0">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                <a href="{{ route('login') }}">Log in</a> to RSVP for this event.
                            </p>
                        @elseif($userRsvp)
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                You're <strong>{{ $userRsvp->status }}</strong> this event.
                                @if($userRsvp->guests > 0)
                                    <br>Bringing {{ $userRsvp->guests }} guest(s).
                                @endif
                            </div>
                            <form method="POST" action="{{ route('events.rsvp.cancel', $event) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    Cancel RSVP
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('events.rsvp', $event) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-semibold">RSVP Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="going">I'm Going</option>
                                        <option value="interested">Interested</option>
                                        <option value="not_going">Not Going</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="guests" class="form-label fw-semibold">Number of Guests</label>
                                    <input type="number" id="guests" name="guests"
                                           class="form-control"
                                           value="{{ old('guests', 0) }}" min="0">
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label fw-semibold">Notes (optional)</label>
                                    <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Submit RSVP
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendees Section -->
    @if($attendees->isNotEmpty())
        <div class="mt-5">
            <h3 class="fw-bold text-secondary mb-4">
                <i class="bi bi-people"></i> Who's Going
            </h3>
            <div class="row g-3">
                @foreach($attendees as $rsvp)
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded shadow-sm">
                            <img src="{{ $rsvp->user->profile_photo_url }}"
                                 alt="{{ $rsvp->user->name }}"
                                 class="rounded-circle me-3" width="45" height="45">
                            <div>
                                <p class="mb-0 fw-semibold text-dark">{{ $rsvp->user->name }}</p>
                                @if($rsvp->guests > 0)
                                    <small class="text-muted">+{{ $rsvp->guests }} guest(s)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
