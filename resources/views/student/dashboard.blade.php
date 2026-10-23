@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-user-graduate me-2"></i> Student Dashboard
    </h2>
</div>
@endsection

@section('content')
<div class="row g-4">
    <!-- Profile Summary -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title text-dark">Welcome, {{ $user->name }}!</h4>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Student ID</p>
                        <p class="fw-medium">{{ $user->student_id ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Graduation Year</p>
                        <p class="fw-medium">{{ $user->graduation_year ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Program</p>
                        <p class="fw-medium">{{ $user->program ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Email</p>
                        <p class="fw-medium">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title text-dark">Quick Actions</h5>
                <div class="list-group">
                    @can('view-events')
                    <a href="{{ route('events.index') }}" class="list-group-item list-group-item-action d-flex align-items-start">
                        <i class="fa-solid fa-calendar-days text-primary me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">View Upcoming Events</div>
                            <small class="text-muted">See alumni and career events</small>
                        </div>
                    </a>
                    @endcan

                    <a href="{{ route('jobs.index') }}" class="list-group-item list-group-item-action d-flex align-items-start">
                        <i class="fa-solid fa-briefcase text-success me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">Browse Job Opportunities</div>
                            <small class="text-muted">Find jobs and internships</small>
                        </div>
                    </a>

                    <a href="{{ route('student.profile') }}" class="list-group-item list-group-item-action d-flex align-items-start">
                        <i class="fa-solid fa-user-edit text-warning me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">Update Profile</div>
                            <small class="text-muted">Keep your information current</small>
                        </div>
                    </a>

                    @can('view-forums')
                    <a href="{{ route('forum.index') }}" class="list-group-item list-group-item-action d-flex align-items-start">
                        <i class="fa-solid fa-comments text-info me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">Visit Discussion Forum</div>
                            <small class="text-muted">Connect with peers and alumni</small>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Events -->
@can('view-events')
<div class="mt-5">
    <h3 class="fw-semibold text-dark mb-3">Upcoming Events</h3>
    <div class="row g-4">
        @forelse($upcomingEvents as $event)
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $event->title }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ $event->start_date->format('M j, Y g:i A') }}
                    </p>
                    <p class="text-truncate-2 text-secondary small">{{ $event->description }}</p>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary mt-2">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">No upcoming events scheduled</p>
        @endforelse
    </div>
    @if($upcomingEvents->count() > 0)
    <div class="mt-3 text-end">
        <a href="{{ route('events.index') }}" class="btn btn-link text-primary">View All Events →</a>
    </div>
    @endif
</div>
@endcan

<!-- Recent Jobs -->
<div class="mt-5">
    <h3 class="fw-semibold text-dark mb-3">Recent Job Postings</h3>
    <div class="row g-4">
        @forelse($recentJobs as $job)
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">{{ $job->title }}</h5>
                    <p class="small text-muted mb-1">{{ $job->company }}</p>
                    <p class="small text-secondary mb-2">
                        <i class="fa-solid fa-location-dot me-1"></i> {{ $job->location }}
                        <span class="mx-1">•</span> {{ $job->employment_type }}
                    </p>
                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-sm btn-outline-success mt-2">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">No recent job postings available</p>
        @endforelse
    </div>
    @if($recentJobs->count() > 0)
    <div class="mt-3 text-end">
        <a href="{{ route('jobs.index') }}" class="btn btn-link text-success">View All Jobs →</a>
    </div>
    @endif
</div>
@endsection
