@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">{{ $job->title }}</h2>
    @can('update', $job)
    <div class="d-flex gap-2">
        <a href="{{ route('jobs.edit', $job) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-pen me-1"></i> Edit
        </a>
        <form method="POST" action="{{ route('jobs.destroy', $job) }}" onsubmit="return confirm('Are you sure you want to delete this job posting?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-trash me-1"></i> Delete
            </button>
        </form>
    </div>
    @endcan
</div>
@endsection

@section('content')
<div class="row py-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <!-- Job Header -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
                    <div class="flex-grow-1">
                        <h1 class="h4 fw-bold">{{ $job->title }}</h1>
                        <p class="text-muted mb-2">{{ $job->company }} • {{ $job->location }} {{ $job->is_remote ? '(Remote)' : '' }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">{{ ucfirst($job->type) }}</span>
                            @if($job->is_remote)
                                <span class="badge bg-success">Remote</span>
                            @endif
                            @if($job->employment_type)
                                <span class="badge bg-info text-dark">{{ $job->employment_type }}</span>
                            @endif
                            @if($job->salary_range)
                                <span class="badge bg-purple text-white">{{ $job->salary_range }}</span>
                            @endif
                            @if($job->is_expired)
                                <span class="badge bg-danger">Expired</span>
                            @elseif(!$job->is_active)
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-md-end mt-3 mt-md-0">
                        <p class="small text-muted mb-1">Posted {{ $job->created_at->diffForHumans() }}</p>
                        @if($job->application_deadline)
                            <p class="small {{ $job->is_expired ? 'text-danger' : 'text-muted' }}">
                                Deadline: {{ $job->application_deadline->format('M j, Y') }}
                            </p>
                        @endif
                        <p class="small text-muted">Posted by: {{ $job->poster->name }}</p>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Left: Job Description & Skills -->
                    <div class="col-md-8">
                        <!-- Job Description -->
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-2">Job Description</h5>
                            <p>{!! nl2br(e($job->description)) !!}</p>
                        </div>

                        <!-- Required Skills -->
                        @php($skillsRequired = is_array($job->skills_required) ? $job->skills_required : array_filter(array_map('trim', explode(',', $job->skills_required)))) @if(count($skillsRequired))
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-2">Required Skills</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($skillsRequired as $skill)
                                    <span class="badge bg-light text-dark">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Preferred Skills -->
                        @php($skillsPreferred = is_array($job->skills_preferred) ? $job->skills_preferred : array_filter(array_map('trim', explode(',', $job->skills_preferred)))) @if(count($skillsPreferred))
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-2">Preferred Skills</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($skillsPreferred as $skill)
                                    <span class="badge bg-info text-dark">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right: Application Box & Job Details -->
                    <div class="col-md-4">
                        <!-- Application Box -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                @if($userApplication)
                                    <div class="text-center">
                                        <i class="fa-solid fa-circle-check fa-2x text-primary mb-2"></i>
                                        <h6 class="fw-semibold">Application Submitted</h6>
                                        <p class="small text-muted">You've already applied to this position.</p>
                                        @if($userApplication->notes)
                                        <p class="small text-primary mt-2">{{ $userApplication->notes }}</p>
                                        @endif
                                    </div>
                                @elseif($job->canApply())
                                    <h6 class="fw-semibold mb-3">Apply for this position</h6>
                                    <form method="POST" action="{{ route('jobs.apply', $job) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="cover_letter" class="form-label">Cover Letter *</label>
                                            <textarea class="form-control" id="cover_letter" name="cover_letter" rows="5" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="resume" class="form-label">Resume *</label>
                                            <input class="form-control" type="file" id="resume" name="resume" required accept=".pdf,.doc,.docx">
                                            <small class="text-muted">PDF, DOC, or DOCX (Max: 2MB)</small>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                                    </form>
                                @elseif($job->is_expired)
                                    <div class="text-center text-danger">
                                        <i class="fa-solid fa-ban fa-2x mb-2"></i>
                                        <h6 class="fw-semibold">Application Closed</h6>
                                        <p class="small text-muted">The deadline for this position has passed.</p>
                                    </div>
                                @elseif(!$job->is_active)
                                    <div class="text-center text-warning">
                                        <i class="fa-solid fa-triangle-exclamation fa-2x mb-2"></i>
                                        <h6 class="fw-semibold">Not Accepting Applications</h6>
                                        <p class="small text-muted">This position is not currently active.</p>
                                    </div>
                                @else
                                    <div class="text-center text-primary">
                                        <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
                                        <h6 class="fw-semibold">Login to Apply</h6>
                                        <p class="small text-muted">You need to be logged in to apply.</p>
                                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm mt-2">Sign In</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Job Details</h6>
                                <ul class="list-unstyled mb-0">
                                    @if($job->type)
                                    <li><strong>Type:</strong> {{ ucfirst($job->type) }}</li>
                                    @endif
                                    @if($job->employment_type)
                                    <li><strong>Employment:</strong> {{ $job->employment_type }}</li>
                                    @endif
                                    @if($job->salary_range)
                                    <li><strong>Salary:</strong> {{ $job->salary_range }}</li>
                                    @endif
                                    @if($job->application_deadline)
                                    <li><strong>Deadline:</strong> {{ $job->application_deadline->format('M j, Y') }}</li>
                                    @endif
                                    @if($job->contact_email)
                                    <li><strong>Email:</strong> <a href="mailto:{{ $job->contact_email }}">{{ $job->contact_email }}</a></li>
                                    @endif
                                    @if($job->contact_phone)
                                    <li><strong>Phone:</strong> {{ $job->contact_phone }}</li>
                                    @endif
                                    @if($job->website)
                                    <li><strong>Website:</strong> <a href="{{ $job->website }}" target="_blank">{{ $job->website }}</a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <!-- View Applications -->
                        @can('viewApplications', $job)
                        <div class="mt-3">
                            <a href="{{ route('jobs.applications', $job) }}" class="btn btn-outline-primary w-100">
                                View Applications ({{ $job->applications_count ?? $job->applications()->count() }})
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
