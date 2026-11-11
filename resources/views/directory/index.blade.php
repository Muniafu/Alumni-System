@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-address-book me-2"></i> Alumni & Student Directory
    </h2>
</div>
@endsection

@section('content')
<div class="container-fluid py-4">

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('directory.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label fw-medium">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               class="form-control" placeholder="Name, email, skills, etc.">
                    </div>
                    <div class="col-md-3">
                        <label for="graduation_year" class="form-label fw-medium">Graduation Year</label>
                        <select name="graduation_year" id="graduation_year" class="form-select">
                            <option value="">All Years</option>
                            @foreach($graduationYears as $year)
                                <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="program" class="form-label fw-medium">Program</label>
                        <select name="program" id="program" class="form-select">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                    {{ $program }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="role" class="form-label fw-medium">Role</label>
                        <select name="role" id="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="alumni" {{ request('role') == 'alumni' ? 'selected' : '' }}>Alumni</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Students</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search me-1"></i> Search
                    </button>
                    <a href="{{ route('directory.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    @if($users->isEmpty())
        <div class="alert alert-info shadow-sm border-0">
            <i class="fa-solid fa-circle-info me-2"></i> No users found matching your criteria.
        </div>
    @else
        <div class="row g-4">
            @foreach($users as $user)
                <div class="col-md-4 col-lg-3">
                    <div class="card shadow-sm border-0 h-100 hover-shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center me-3"
                                     style="width: 64px; height: 64px; font-size: 1.25rem;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">
                                        <a href="{{ route('directory.show', $user) }}" class="text-decoration-none text-primary fw-semibold">
                                            {{ $user->name }}
                                        </a>
                                    </h5>
                                    <p class="mb-0 text-secondary small">{{ $user->program }}</p>
                                    <p class="text-muted small">Class of {{ $user->graduation_year }}</p>
                                </div>
                            </div>

                            @if($user->profile)
                                <ul class="list-unstyled mb-3">
                                    @if($user->profile->current_job)
                                    <li>
                                        <small class="text-muted">Current Job:</small>
                                        <div class="fw-medium">{{ $user->profile->current_job }}</div>
                                    </li>
                                    @endif
                                    @if($user->profile->company)
                                    <li class="mt-2">
                                        <small class="text-muted">Company:</small>
                                        <div class="fw-medium">{{ $user->profile->company }}</div>
                                    </li>
                                    @endif
                                    @php($userSkills = $user->profile->skills_array ?? [])
                                    @if(count($userSkills) > 0)
                                    <li class="mt-2">
                                        <small class="text-muted">Skills:</small>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach(array_slice($userSkills, 0, 3) as $skill)
                                                <span class="badge bg-light text-dark border">{{ $skill }}</span>
                                            @endforeach
                                            @if(count($userSkills) > 3)
                                                <span class="badge bg-light text-dark border">
                                                    +{{ count($userSkills) - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    </li>
                                    @endif
                                </ul>
                            @endif

                            <div class="text-end">
                                <a href="{{ route('directory.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
