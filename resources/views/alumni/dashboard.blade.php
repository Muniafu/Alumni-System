@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-user-graduate me-2"></i> Alumni Dashboard
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
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Education</p>
                        <p class="fw-medium">{{ $user->profile->education ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Certifications</p>
                        <p class="fw-medium">{{ $user->profile->certifications ? implode(', ', $user->profile->certifications_array) : 'Not provided' }}</p>
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

                    <a href="{{ route('alumni.profile') }}" class="list-group-item list-group-item-action d-flex align-items-start"> {{-- Fixed route --}}
                        <i class="fa-solid fa-user-edit text-warning me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">Update Profile</div>
                            <small class="text-muted">Keep your information current</small>
                        </div>
                    </a>

                    @can('view-forums') {{-- Fixed gate --}}
                    <a href="{{ route('forum.index') }}" class="list-group-item list-group-item-action d-flex align-items-start">
                        <i class="fa-solid fa-comments text-info me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">Visit Discussion Forum</div>
                            <small class="text-muted">Connect with peers and alumni</small>
                        </div>
                    </a>
                    @endcan

                    @can('view-mentorship') {{-- Fixed gate --}}
                    <a href="{{ route('mentorship.index') }}" class="list-group-item list-group-item-action d-flex align-items-start">
                        <i class="fa-solid fa-hands-helping text-danger me-3 fs-4"></i>
                        <div>
                            <div class="fw-medium">Mentorship Program</div>
                            <small class="text-muted">Connect with alumni mentors</small>
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
    @can('create-events')
        <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm">Create Event</a>
        @endcan
    <div class="row g-4">
        @forelse($upcomingAlumniEvents as $event) {{-- Fixed variable --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $event->title }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ $event->start->format('M j, Y g:i A') }} {{-- Assume start is date --}}
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
    @if($upcomingAlumniEvents->count() > 0)
    <div class="mt-3 text-end">
        <a href="{{ route('events.index') }}" class="btn btn-link text-primary">View All Events →</a>
    </div>
    @endif

    <!-- Your Events Preview -->
    @if($userEvents->count() > 0)
    <div class="mt-4">
        <h5 class="text-secondary">Your Events</h5>
        <div class="row g-3">
            @foreach($userEvents->take(3) as $event)
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>{{ $event->title }}</h6>
                        <small class="text-muted">{{ $event->start->format('M j, Y') }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endcan

<!-- Recent Jobs -->
<div class="mt-5">
    <h3 class="fw-semibold text-dark mb-3">Recent Job Postings</h3>
    @can('create-jobs')
        <a href="{{ route('jobs.create') }}" class="btn btn-success btn-sm">Post a Job</a>
        @endcan
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

<!-- Mentorships Section -->
@can('view-mentorship')
<div class="mt-5">
    <h3 class="fw-semibold text-dark mb-3">Mentorship Opportunities</h3>
    @can('create-mentorship')
    <a href="{{ route('mentorship.create') }}" class="btn btn-danger btn-sm">Offer Mentorship</a>
    @endcan

    {{-- Mentorships Requested --}}
    <h5 class="text-secondary mb-2">Mentorships You Receive</h5>
    @if($mentorshipsRequested && $mentorshipsRequested->count() > 0)
        <div class="row g-3 mb-4">
            @foreach($mentorshipsRequested->take(3) as $mentorship)
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold">Mentored by {{ $mentorship->mentor->name }}</h6>
                        <p class="small text-secondary">
                            Started {{ $mentorship->start_date->format('M j, Y') }}
                            @if($mentorship->end_date)
                                • Ended {{ $mentorship->end_date->format('M j, Y') }}
                            @endif
                        </p>
                        <span class="badge
                            @if($mentorship->status === 'active') bg-success
                            @elseif($mentorship->status === 'completed') bg-primary
                            @else bg-info text-dark @endif
                            text-capitalize mb-2">
                            {{ $mentorship->status }}
                        </span>
                        <p class="small text-secondary">{{ Str::limit($mentorship->goal, 80) }}</p>
                        <a href="{{ route('mentorship.show', $mentorship) }}" class="small text-primary">View Details</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($mentorshipsRequested->count() > 3)
        <div class="text-end mb-4">
            <a href="{{ route('mentorship.index') }}" class="btn btn-link text-primary">View All Your Mentorships →</a>
        </div>
        @endif
    @else
        <div class="alert alert-info mb-4">
            <p class="mb-2">You are not currently receiving mentorship.</p>
            <a href="{{ route('mentorship.find') }}" class="btn btn-primary mt-2">Find a Mentor</a>
        </div>
    @endif

    {{-- Mentorships Offered --}}
    <h5 class="text-secondary mb-2">Mentorships You Offer</h5>
    @if($mentorshipsOffered && $mentorshipsOffered->count() > 0)
        <div class="row g-3">
            @foreach($mentorshipsOffered->take(3) as $mentorship)
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold">Mentoring {{ $mentorship->mentee->name }}</h6>
                        <p class="small text-secondary">
                            Started {{ $mentorship->start_date->format('M j, Y') }}
                            @if($mentorship->end_date)
                                • Ended {{ $mentorship->end_date->format('M j, Y') }}
                            @endif
                        </p>
                        <span class="badge
                            @if($mentorship->status === 'active') bg-success
                            @elseif($mentorship->status === 'completed') bg-primary
                            @else bg-info text-dark @endif
                            text-capitalize mb-2">
                            {{ $mentorship->status }}
                        </span>
                        <p class="small text-secondary">{{ Str::limit($mentorship->goal, 80) }}</p>
                        <a href="{{ route('mentorship.show', $mentorship) }}" class="small text-primary">View Details</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($mentorshipsOffered->count() > 3)
        <div class="text-end mt-3">
            <a href="{{ route('mentorship.index') }}" class="btn btn-link text-primary">View All Your Mentorships →</a>
        </div>
        @endif
    @else
        <div class="alert alert-warning">
            <p class="mb-0">You are not currently mentoring anyone.</p>
        </div>
    @endif
</div>
@endcan

<!-- Forum Categories -->
@can('view-forums')
<div class="mt-5">
    <h3 class="fw-semibold text-dark mb-3">Forum Categories</h3>
    @can('create-forums')
        <a href="{{ ($forumCategories && $forumCategories->isNotEmpty()) ? route('forum.threads.create', $forumCategories->first()) : '#' }}" class="btn btn-info btn-sm">Create Thread</a>
    @endcan
    @if($forumCategories && $forumCategories->count() > 0)
        <div class="row g-3">
            @foreach($forumCategories->take(3) as $category)
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="fw-semibold">
                                <a href="{{ route('forum.categories.show', $category) }}" class="text-decoration-none text-primary">
                                    {{ $category->name }}
                                </a>
                            </h6>
                            <p class="small text-muted">{{ Str::limit($category->description, 80) }}</p>
                            <small class="text-secondary">
                                {{ $category->threads_count }} threads • {{ $category->posts_count }} posts
                            </small>
                            <a href="{{ route('forum.categories.show', $category) }}" class="small text-primary d-block mt-2">View Threads →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-end mt-3">
            <a href="{{ route('forum.categories.index') }}" class="btn btn-link text-primary fw-medium">View All Categories →</a>
        </div>
    @else
        <p class="text-muted">No forum categories available.</p>
    @endif
</div>
@endcan
@endsection
