@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-calendar-days me-2"></i> Upcoming Events
    </h2>
    @guest
    <div class="d-flex gap-2">
        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Log in</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Join the network</a>
    </div>
    @endguest
@endsection

@section('content')
<div class="container py-4">
    @if($events->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body text-center text-muted py-5">
                <i class="fa-regular fa-calendar-xmark fa-2x mb-3"></i>
                <p class="mb-0">No upcoming events right now. Check back soon!</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush">
                @foreach($events as $event)
                <div class="list-group-item py-4 px-3">
                    <div class="row g-4 align-items-center">
                        @if($event->image)
                        <div class="col-md-3">
                            <img src="{{ Storage::url($event->image) }}"
                                 alt="{{ $event->title }}"
                                 class="img-fluid rounded shadow-sm">
                        </div>
                        @endif
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="fw-semibold text-dark mb-0">
                                    <a href="{{ route('events.show', $event) }}"
                                       class="text-decoration-none text-dark">
                                        {{ $event->title }}
                                    </a>
                                </h5>
                                @if($event->is_online)
                                <span class="badge bg-primary">Online Event</span>
                                @endif
                            </div>
                            <p class="mb-1 text-muted small">
                                <i class="fa-regular fa-clock me-2"></i>
                                {{ $event->start->format('l, F j, Y g:i a') }}
                                – {{ $event->end->format('g:i a') }}
                            </p>
                            @if($event->location)
                            <p class="mb-1 text-muted small">
                                <i class="fa-solid fa-location-dot me-2"></i>
                                {{ $event->location }}
                            </p>
                            @endif
                            @if($event->organizer)
                            <p class="mb-0 small text-muted">
                                <i class="fa-solid fa-user-tie me-1"></i>
                                Organized by {{ $event->organizer->name }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="card-footer bg-light d-flex justify-content-center">
                {{ $events->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @guest
        <div class="alert alert-info mt-4">
            <i class="fa-solid fa-circle-info me-2"></i>
            <a href="{{ route('register') }}">Create an account</a>
            to RSVP for events and see who's going.
        </div>
        @endguest
    @endif
</div>
@endsection
