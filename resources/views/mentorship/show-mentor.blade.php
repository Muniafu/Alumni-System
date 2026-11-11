@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-user-tie me-2"></i> {{ $mentor->name }} - Mentor Profile
    </h2>
    <a href="{{ route('mentorship.find') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Mentors
    </a>
</div>
@endsection

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <!-- Mentor Avatar -->
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-3 mx-auto"
                         style="width: 120px; height: 120px; font-weight: 600; font-size: 2.5rem;">
                        {{ strtoupper(substr($mentor->name, 0, 1)) }}
                    </div>

                    <!-- Name & Role -->
                    <h3 class="fw-bold mb-1">{{ $mentor->name }}</h3>
                    <p class="text-muted mb-0">
                        {{ $mentor->profile->current_job ?? 'Alumni' }} at {{ $mentor->profile->company ?? 'Unknown' }}
                    </p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <!-- Professional Info -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fa-solid fa-briefcase me-1"></i> Professional Info</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Current Position:</strong> {{ $mentor->profile->current_job ?? 'Not specified' }}</p>
                            <p><strong>Company:</strong> {{ $mentor->profile->company ?? 'Not specified' }}</p>
                            <p><strong>Graduation Year:</strong> {{ $mentor->graduation_year ?? 'Not specified' }}</p>
                            <p><strong>Program:</strong> {{ $mentor->program ?? 'Not specified' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Skills & Expertise -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fa-solid fa-lightbulb me-1"></i> Skills & Expertise</h5>
                        </div>
                        <div class="card-body">
                            @php($mentorSkills = $mentor->profile?->skills_array ?? [])
                            @if (count($mentorSkills) > 0)
                                <div class="d-flex flex-wrap">
                                    @foreach ($mentorSkills as $skill)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-secondary mb-0">No skills listed.</p>                                
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Section -->
            @if($mentor->profile->bio)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-info-circle me-1"></i> About</h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary">{{ $mentor->profile->bio }}</p>
                </div>
            </div>
            @endif

            <!-- Mentorship Request -->
            @if(!$existingRequest)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-envelope me-1"></i> Request Mentorship</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('mentorship.send-request', $mentor) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="goal" class="form-label">What do you hope to achieve through this mentorship?</label>
                            <textarea class="form-control" id="goal" name="goal" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message to Mentor</label>
                            <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                            <div class="form-text">
                                Briefly introduce yourself and explain why you'd like this mentor's guidance.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-paper-plane me-1"></i> Send Request
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="alert @if($existingRequest->status === 'pending') alert-info
                               @elseif($existingRequest->status === 'accepted') alert-success
                               @else alert-secondary @endif mt-3">
                @if($existingRequest->status === 'pending')
                    You have a pending mentorship request with this mentor.
                @elseif($existingRequest->status === 'accepted')
                    Your mentorship request has been accepted!
                    <a href="{{ route('mentorship.show', $existingRequest->mentorship) }}" class="alert-link">View Mentorship</a>
                @else
                    Your mentorship request was declined.
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
