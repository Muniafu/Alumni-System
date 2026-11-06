@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold text-primary mb-4">
        {{ ($mentorProfile->exists ?? false) ? 'Update Your Mentor Profile' : 'Sign Up as a Mentor' }}
    </h2>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('mentorship.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="expertise" class="form-label">Your Expertise</label>
                    <input type="text" class="form-control" id="expertise" name="expertise"
                           value="{{ old('expertise', $mentorProfile->expertise ?? '') }}" required>
                    <div class="form-text">e.g. Software Engineering, Finance, Public Health</div>
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Bio / Experience</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4" required>{{ old('bio', $mentorProfile->bio ?? '') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    {{ ($mentorProfile->exists ?? false) ? 'Update' : 'Sign Up' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
