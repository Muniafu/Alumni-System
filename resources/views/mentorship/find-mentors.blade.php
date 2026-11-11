@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-user-tie me-2"></i> Find Mentors
    </h2>
    @can('create-mentorship')
        <a href="{{ route('mentorship.create') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-plus me-1"></i> Sign Up as a Mentor
        </a>
    @endcan
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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h4 class="mb-1">Available Mentors</h4>
                    <p class="mb-0 text-secondary">Browse alumni who have signed up to mentor</p>
                </div>

                <div class="card-body">
                    @if($mentors->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="fa-solid fa-info-circle me-2"></i>No mentors have signed up yet.
                        </div>
                    @else
                        <div class="row">
                            @foreach($mentors as $mentor)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 shadow-sm hover-shadow border-0">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                                     style="width: 60px; height: 60px; font-weight: 600; font-size: 1.25rem;">
                                                    {{ strtoupper(substr($mentor->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">{{ $mentor->name }}</h5>
                                                    <p class="text-secondary mb-0">
                                                        {{ $mentor->mentorProfile->expertise }}
                                                    </p>
                                                </div>
                                            </div>

                                            @if($mentor->mentorProfile->bio)
                                                <p class="text-secondary small mb-3">{{ Str::limit($mentor->mentorProfile->bio, 120) }}</p>
                                            @endif

                                            @php($mentorSkills = $mentor->profile->skills_array ?? [])
                                            @if (count($mentorSkills) > 0)
                                                <div class="mb-3">
                                                    <h6 class="fw-medium">Skills:</h6>
                                                    <div class="d-flex flex-wrap">
                                                        @foreach (array_slice($mentorSkills, 0, 5) as $skill)
                                                            <span class="badge bg-success text-light me-1 mb-1">{{ $skill }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            <a href="{{ route('mentorship.show-mentor', $mentor) }}"
                                               class="btn btn-primary mt-auto">
                                                <i class="fa-solid fa-arrow-right me-1"></i> View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $mentors->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        transition: box-shadow 0.3s ease;
    }
</style>
@endsection
