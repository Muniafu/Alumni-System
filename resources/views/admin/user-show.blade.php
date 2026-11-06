@extends('layouts.app')

@section('header')
<h2 class="fw-semibold fs-4 text-dark">
    <i class="bi bi-person-lines-fill text-primary me-2"></i> {{ __('User Details') }}
</h2>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <img src="{{ $user->profile_photo_url }}" class="rounded-circle me-3" width="60" height="60" alt="User Avatar">
                    <div>
                        <h5 class="fw-bold text-primary mb-1">{{ $user->name }}</h5>
                        <span class="badge {{ $user->is_approved ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $user->is_approved ? 'Approved' : 'Pending Approval' }}
                        </span>
                        <br>
                        <small class="text-muted">Roles: {{ $user->roles->pluck('name')->join(', ') ?: 'N/A' }}</small>
                    </div>
                </div>
                <a href="{{ route('admin.user-management') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Users
                </a>
            </div>

            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-3" id="userTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#basic" role="tab">Basic Info</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#profile" role="tab">Profile</a></li>
                @if($user->hasAnyRole(['student','alumni']))
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#academic" role="tab">Academic</a></li>
                @endif
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#activity" role="tab">Activity</a></li>
                @if(auth()->user()->can('manage-permissions') && $user->hasRole('admin'))
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#permissions" role="tab">Permissions</a></li>
                @endif
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content">

                {{-- Basic Info --}}
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Name:</strong> {{ $user->name }}</li>
                        <li class="list-group-item"><strong>Email:</strong> {{ $user->email }}</li>
                        <li class="list-group-item"><strong>Registered:</strong> {{ $user->created_at->format('M d, Y h:i A') }}</li>
                        <li class="list-group-item"><strong>Last Login:</strong> {{ $user->last_login_at?->diffForHumans() ?? 'N/A' }}</li>
                        <li class="list-group-item"><strong>Roles:</strong> {{ $user->roles->pluck('name')->join(', ') ?: 'N/A' }}</li>
                    </ul>
                </div>

                {{-- Profile --}}
                <div class="tab-pane fade" id="profile" role="tabpanel">
                    @if($user->profile)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Current Job:</strong> {{ $user->profile->current_job ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Company:</strong> {{ $user->profile->company ?? 'N/A' }}</li>
                            {{-- skills is stored as a comma-separated string — implode() on it crashed this page --}}
                            <li class="list-group-item"><strong>Skills:</strong> {{ $user->profile->skills ?: 'N/A' }}</li>
                        </ul>
                    @else
                        <p class="text-muted">No profile information available.</p>
                    @endif
                </div>

                {{-- Academic --}}
                @if($user->hasAnyRole(['student','alumni']))
                    <div class="tab-pane fade" id="academic" role="tabpanel">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Student ID:</strong> {{ $user->student_id ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Graduation Year:</strong> {{ $user->graduation_year ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Program:</strong> {{ $user->program ?? 'N/A' }}</li>
                        </ul>
                    </div>
                @endif

                {{-- Activity --}}
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    <div class="accordion" id="activityAccordion">

                        {{-- Events --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingEvents">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvents">
                                    Events Attended ({{ $user->events->count() }})
                                </button>
                            </h2>
                            <div id="collapseEvents" class="accordion-collapse collapse" data-bs-parent="#activityAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        @forelse($user->events as $event)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <strong>{{ $event->title }}</strong>
                                                    <small class="text-muted">({{ $event->start->format('M d, Y') }} - {{ $event->end->format('M d, Y') }})</small>
                                                    <br>
                                                    <span class="badge
                                                        @if($event->pivot->status === 'going') bg-success
                                                        @elseif($event->pivot->status === 'interested') bg-warning
                                                        @else bg-secondary @endif">
                                                        {{ ucfirst($event->pivot->status) }}
                                                    </span>
                                                </span>
                                                <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            </li>
                                        @empty
                                            <p class="text-muted mb-0">No events attended yet.</p>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Job Applications --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingJobs">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseJobs">
                                    Job Applications ({{ $user->jobApplications->count() }})
                                </button>
                            </h2>
                            <div id="collapseJobs" class="accordion-collapse collapse" data-bs-parent="#activityAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        @forelse($user->jobApplications as $application)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <strong>{{ $application->jobPosting?->title ?? 'Deleted posting' }}</strong>
                                                    <small class="text-muted">Applied: {{ $application->created_at->format('M d, Y') }}</small>
                                                </span>
                                                @if($application->jobPosting)
                                                    <a href="{{ route('jobs.show', $application->jobPosting) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                @endif
                                            </li>
                                        @empty
                                            <p class="text-muted mb-0">No job applications yet.</p>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Forum Posts --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPosts">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePosts">
                                    Forum Posts ({{ $user->forumPosts->count() }})
                                </button>
                            </h2>
                            <div id="collapsePosts" class="accordion-collapse collapse" data-bs-parent="#activityAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        @forelse($user->forumPosts as $post)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <strong>{{ Str::limit($post->content, 50) }}</strong>
                                                    <small class="text-muted">in {{ $post->thread?->title ?? 'Deleted thread' }}</small>
                                                </span>
                                                @if($post->thread)
                                                    {{-- The resource route is forum.threads.show (plural),
                                                         not forum.thread.show --}}
                                                    <a href="{{ route('forum.threads.show', $post->thread) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                @endif
                                            </li>
                                        @empty
                                            <p class="text-muted mb-0">No forum posts yet.</p>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Permissions: read-only per-user view. The old tab posted to a
                     route that never existed (admin.user.update-permissions) —
                     but this app authorizes by ROLE, so per-user permission
                     sync would not affect any gate anyway. Role permissions are
                     managed on the dedicated permissions page. --}}
                @if(auth()->user()->can('manage-permissions') && $user->hasRole('admin'))
                    <div class="tab-pane fade" id="permissions" role="tabpanel">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <h6 class="fw-semibold text-dark mb-3">Roles & Their Permissions</h6>

                                @foreach($user->roles as $role)
                                    <div class="mb-3">
                                        <span class="badge bg-primary mb-1">{{ ucfirst($role->name) }}</span>
                                        <ul class="mb-0">
                                            @forelse($role->permissions as $permission)
                                                <li>{{ $permission->name }}</li>
                                            @empty
                                                <li class="text-muted">No permissions attached to this role.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                @endforeach

                                <a href="{{ route('admin.permissions') }}" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="bi bi-shield-lock me-1"></i> Manage Role Permissions
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Actions --}}
            @unless($user->trashed())
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>
                    <form action="{{ route('admin.user.delete', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            @endunless

        </div>
    </div>
</div>
@endsection
