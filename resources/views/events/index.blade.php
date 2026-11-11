@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-calendar-days me-2"></i> Events
    </h2>
</div>
@endsection

@section('content')
<div class="container py-4">
    <!-- Header Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-semibold text-dark">Upcoming Events</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('events.calendar') }}"
               class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                <i class="fa-solid fa-calendar me-2"></i> Calendar View
            </a>
            @can('create', App\Models\Event::class)
            <a href="{{ route('events.create') }}"
               class="btn btn-primary btn-sm d-flex align-items-center">
                <i class="fa-solid fa-plus me-2"></i> Create Event
            </a>
            @endcan
        </div>
    </div>

    <!-- Events List -->
    <div class="card shadow-sm border-0">
        @if($events->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="fa-regular fa-calendar-xmark fa-2x mb-3"></i>
                <p class="mb-0">No upcoming events found.</p>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($events as $event)
                <div class="list-group-item py-4 px-3">
                    <div class="row g-4 align-items-center">
                        <!-- Event Image -->
                        @if($event->image)
                        <div class="col-md-3">
                            <img src="{{ Storage::url($event->image) }}"
                                 alt="{{ $event->title }}"
                                 class="img-fluid rounded shadow-sm">
                        </div>
                        @endif

                        <!-- Event Details -->
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-semibold text-dark mb-0">
                                    <a href="{{ route('events.show', $event) }}"
                                       class="text-decoration-none text-dark hover-text-primary">
                                        {{ $event->title }}
                                    </a>
                                </h5>
                                @if($event->is_online)
                                <span class="badge bg-primary">Online Event</span>
                                @endif
                            </div>

                            <!-- Date & Time -->
                            <p class="mb-1 text-muted small">
                                <i class="fa-regular fa-clock me-2"></i>
                                {{ $event->start->format('l, F j, Y g:i a') }}
                                – {{ $event->end->format('g:i a') }}
                            </p>

                            <!-- Location -->
                            @if($event->location)
                            <p class="mb-1 text-muted small">
                                <i class="fa-solid fa-location-dot me-2"></i>
                                {{ $event->location }}
                            </p>
                            @endif

                            <!-- Organizer & Attendees -->
                            <div class="d-flex flex-wrap gap-3 mt-2 small text-muted">
                                <span>
                                    <i class="fa-solid fa-user-tie me-1"></i>
                                    Organized by {{ $event->organizer?->name ?? 'Former member' }}
                                </span>
                                <span>
                                    {{-- Uses the withCount value from the controller (no N+1 count queries) --}}
                                    <i class="fa-solid fa-users me-1"></i>
                                    {{ $event->attendees_going_count }} going
                                    @if($event->capacity)
                                        ({{ max(0, $event->capacity - $event->attendees_going_count) }} spots left)
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-light d-flex justify-content-center">
                {{ $events->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
