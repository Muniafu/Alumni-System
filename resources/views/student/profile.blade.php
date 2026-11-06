@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-id-card me-2"></i> My Profile
    </h2>

    <!-- Profile Completion -->
    <div class="text-end">
        <span class="small fw-medium">Profile Completion:</span>
        <span class="small fw-bold
            @if($user->profile->profile_completion >= 80) text-success
            @elseif($user->profile->profile_completion >= 50) text-warning
            @else text-danger @endif">
            {{ $user->profile->profile_completion }}%
        </span>
        <div class="progress mt-1" style="height: 6px;">
            <div class="progress-bar bg-primary"
                 role="progressbar"
                 style="width: {{ $user->profile->profile_completion }}%;"
                 aria-valuenow="{{ $user->profile->profile_completion }}"
                 aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('student.profile.update') }}">
                    @csrf

                    <div class="row g-4">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="text-dark fw-semibold mb-3">Basic Information</h5>

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input id="name" type="text" name="name"
                                       value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Student ID -->
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID</label>
                                <input id="student_id" type="text"
                                       value="{{ old('student_id', $user->student_id) }}"
                                       class="form-control bg-light" readonly>
                            </div>

                            <!-- Graduation Year -->
                            <div class="mb-3">
                                <label for="graduation_year" class="form-label">Graduation Year</label>
                                <input id="graduation_year" type="text"
                                       value="{{ old('graduation_year', $user->graduation_year) }}"
                                       class="form-control bg-light" readonly>
                            </div>

                            <!-- Program -->
                            <div class="mb-3">
                                <label for="program" class="form-label">Program</label>
                                <input id="program" type="text"
                                       value="{{ old('program', $user->program) }}"
                                       class="form-control bg-light" readonly>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h5 class="text-dark fw-semibold mb-3">Contact Information</h5>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input id="phone" type="text" name="phone"
                                       value="{{ old('phone', $user->profile->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input id="address" type="text" name="address"
                                       value="{{ old('address', $user->profile->address) }}"
                                       class="form-control @error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- About You -->
                        <div class="col-12">
                            <h5 class="text-dark fw-semibold mb-3">About You</h5>

                            <!-- Bio -->
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" rows="3"
                                          class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $user->profile->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Skills -->
                            <div class="mb-3">
                                <label for="skills" class="form-label">Skills (comma separated)</label>
                                <input id="skills" type="text" name="skills"
                                       value="{{ old('skills', implode(', ', $user->profile->skills_array)) }}"
                                       placeholder="e.g., PHP, JavaScript, Project Management"
                                       class="form-control @error('skills') is-invalid @enderror">
                                @error('skills')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Interests -->
                            <div class="mb-3">
                                <label for="interests" class="form-label">Professional Interests (comma separated)</label>
                                <input id="interests" type="text" name="interests"
                                       value="{{ old('interests', implode(', ', $user->profile->interests_array)) }}"
                                       placeholder="e.g., Web Development, Data Science"
                                       class="form-control @error('interests') is-invalid @enderror">
                                @error('interests')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Social Links -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="linkedin" class="form-label">LinkedIn Profile</label>
                                    <input id="linkedin" type="url" name="linkedin"
                                           value="{{ old('linkedin', $user->profile->getSocialLink('linkedin')) }}"
                                           placeholder="https://linkedin.com/in/username"
                                           class="form-control @error('linkedin') is-invalid @enderror">
                                    @error('linkedin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="twitter" class="form-label">Twitter Profile</label>
                                    <input id="twitter" type="url" name="twitter"
                                           value="{{ old('twitter', $user->profile->getSocialLink('twitter')) }}"
                                           placeholder="https://twitter.com/username"
                                           class="form-control @error('twitter') is-invalid @enderror">
                                    @error('twitter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
