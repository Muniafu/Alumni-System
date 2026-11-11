@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top-4">
            <div>
                <h2 class="h4 mb-0 fw-bold">User Profile: {{ $user->name }}</h2>
                <p class="mb-0 small text-light">
                    {{ $user->program }} • Class of {{ $user->graduation_year }}
                </p>
            </div>
            <a href="{{ route('conversations.create', ['recipients' => [$user->id]]) }}"
               class="btn btn-light text-primary fw-semibold shadow-sm">
                <i class="bi bi-chat-dots me-1"></i> Send Message
            </a>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-md-8">
                    @if($user->profile)
                        <!-- About -->
                        @if($user->profile->bio)
                        <div class="mb-4">
                            <h3 class="h5 fw-semibold text-dark">About</h3>
                            <p class="text-muted">{!! nl2br(e($user->profile->bio)) !!}</p>
                        </div>
                        @endif

                        <!-- Professional & Skills -->
                        <div class="row g-4">
                            @if($user->profile->current_job || $user->profile->company)
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100">
                                    <div class="card-body">
                                        <h3 class="h6 fw-bold text-primary mb-3">Professional</h3>
                                        @if($user->profile->current_job)
                                        <p class="mb-1">
                                            <span class="text-muted small">Current Job:</span><br>
                                            <span class="fw-medium">{{ $user->profile->current_job }}</span>
                                        </p>
                                        @endif
                                        @if($user->profile->company)
                                        <p class="mb-0">
                                            <span class="text-muted small">Company:</span><br>
                                            <span class="fw-medium">{{ $user->profile->company }}</span>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($user->profile->skills || $user->profile->interests)
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-3 h-100">
                                    <div class="card-body">
                                        <h3 class="h6 fw-bold text-success mb-3">Skills & Interests</h3>
                                        @php($profileSkills = $user->profile->skills_array ?? [])
                                        @php($profileInterests = $user->profile->interests_array ?? [])
                                        @if(count($profileSkills) > 0)
                                        <div class="mb-3">
                                            <span class="text-muted small">Skills:</span>
                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                @foreach($profileSkills as $skill)
                                                    <span class="badge bg-primary-subtle text-primary fw-medium px-3 py-2 rounded-pill">{{ $skill }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        @if(count($profileInterests) > 0)
                                        <div>
                                            <span class="text-muted small">Interests:</span>
                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                @foreach($profileInterests as $interest)
                                                    <span class="badge bg-purple text-white fw-medium px-3 py-2 rounded-pill">{{ $interest }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted fst-italic">No profile information available.</p>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 80px;">
                        <div class="card-body">
                            <h3 class="h6 fw-bold text-dark mb-3">Contact Information</h3>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <span class="text-muted small d-block">Email</span>
                                    <a href="mailto:{{ $user->email }}"
                                       class="fw-medium text-decoration-none text-primary">
                                        {{ $user->email }}
                                    </a>
                                </li>
                                @if($user->profile && $user->profile->phone)
                                <li class="mb-3">
                                    <span class="text-muted small d-block">Phone</span>
                                    <span class="fw-medium">{{ $user->profile->phone }}</span>
                                </li>
                                @endif
                                @if($user->profile && $user->profile->address)
                                <li>
                                    <span class="text-muted small d-block">Address</span>
                                    <span class="fw-medium">{{ $user->profile->address }}</span>
                                </li>
                                @endif
                            </ul>

                            @if($user->profile && $user->profile->social_links)
                            <div class="pt-3 border-top mt-3">
                                <h3 class="h6 fw-bold text-dark mb-3">Social Links</h3>
                                <ul class="list-unstyled">
                                    @foreach($user->profile->social_links as $platform => $url)
                                        @if($url)
                                        <li class="mb-2">
                                            <span class="text-muted small d-block text-capitalize">{{ $platform }}</span>
                                            <a href="{{ $url }}" target="_blank"
                                               class="fw-medium text-decoration-none text-success">
                                                {{ $url }}
                                            </a>
                                        </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
