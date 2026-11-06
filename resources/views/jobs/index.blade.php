@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">{{ $title }}</h2>
    @can('create-jobs')
    <a href="{{ route('jobs.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Post New Opportunity
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="row py-4">
    <div class="col-12">

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ !request()->has('type') ? 'active text-primary' : 'text-muted' }}" href="{{ route('jobs.index') }}">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->query('type') === 'job' ? 'active text-primary' : 'text-muted' }}" href="{{ route('jobs.index', ['type' => 'job']) }}">Jobs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->query('type') === 'internship' ? 'active text-primary' : 'text-muted' }}" href="{{ route('jobs.index', ['type' => 'internship']) }}">Internships</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->query('type') === 'mentorship' ? 'active text-primary' : 'text-muted' }}" href="{{ route('jobs.index', ['type' => 'mentorship']) }}">Mentorships</a>
            </li>
        </ul>

        <!-- Job Listings -->
        @forelse ($jobs as $job)
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap">
                    <div>
                        <h5 class="card-title mb-1">
                            <a href="{{ route('jobs.show', $job) }}" class="text-decoration-none text-dark fw-bold">
                                {{ $job->title }}
                            </a>
                        </h5>
                        <p class="text-muted mb-2">{{ $job->company }} • {{ $job->location }}</p>

                        <!-- Badges -->
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            <span class="badge bg-info text-dark">{{ ucfirst($job->type) }}</span>
                            @if($job->is_remote)
                            <span class="badge bg-success">Remote</span>
                            @endif
                            @if($job->employment_type)
                            <span class="badge bg-primary">{{ $job->employment_type }}</span>
                            @endif
                            @if($job->salary_range)
                            <span class="badge bg-purple text-white">{{ $job->salary_range }}</span>
                            @endif
                            @if($job->isExpired)
                            <span class="badge bg-danger">Expired</span>
                            @elseif(!$job->is_active)
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Posted {{ $job->created_at->diffForHumans() }}</small>
                        @if($job->application_deadline)
                        <small class="{{ $job->isExpired ? 'text-danger' : 'text-muted' }}">
                            Deadline: {{ $job->application_deadline->format('M j, Y') }}
                        </small>
                        @endif
                    </div>
                </div>

                <p class="card-text mt-3 text-truncate-2">{{ Str::limit($job->description, 200) }}</p>

                <!-- Skills -->
                @php($skillsRequired = is_array($job->skills_required) ? $job->skills_required : array_filter(array_map('trim', explode(',', $job->skills_required)))) @if(count($skillsRequired))
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @foreach(array_slice($skillsRequired, 0, 3) as $skill)
                    <span class="badge bg-light text-dark">{{ $skill }}</span>
                    @endforeach
                    @if(count($skillsRequired) > 3)
                    <span class="badge bg-light text-dark">+{{ count($skillsRequired) - 3 }} more</span>
                    @endif
                </div>
                @endif

                <div class="mt-3 text-end">
                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-primary btn-sm">
                        View Details →
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="fa-solid fa-briefcase fa-3x text-muted mb-3"></i>
            <h5 class="text-muted mb-2">No opportunities found</h5>
            <p class="text-muted">
                There are currently no {{ request()->query('type') ?? 'job' }} postings available.
            </p>
            @can('create-jobs')
            <a href="{{ route('jobs.create') }}" class="btn btn-primary mt-3">
                Post New Opportunity
            </a>
            @endcan
        </div>
        @endforelse

        <!-- Pagination -->
        @if($jobs->hasPages())
        <div class="mt-4">
            {{ $jobs->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif

    </div>
</div>
@endsection
