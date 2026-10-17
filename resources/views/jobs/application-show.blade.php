@extends('layouts.app')

@section('header')
<div class="d-flex align-items-center justify-content-between">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-briefcase me-2"></i> Application for: {{ $job->title }}
    </h2>
</div>
@endsection

@section('content')
<div class="row g-4 py-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <!-- Applicant Information -->
                <h4 class="text-dark fw-semibold mb-3">Applicant Information</h4>
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ $application->applicant->profile_photo_url }}" alt="{{ $application->applicant->name }}" class="rounded-circle me-3" width="60" height="60">
                    <div>
                        <h5 class="mb-0">{{ $application->applicant->name }}</h5>
                        <small class="text-muted">{{ $application->applicant->email }}</small>
                        @if($application->applicant->profile)
                            <p class="mb-0 text-secondary">{{ $application->applicant->profile->current_job }}</p>
                            <p class="mb-0 text-secondary">{{ $application->applicant->profile->company }}</p>
                        @endif
                    </div>
                </div>

                <!-- Application Details -->
                <h4 class="text-dark fw-semibold mb-3">Application Details</h4>
                <div class="mb-4">
                    <p class="mb-2"><strong>Status:</strong>
                        @php
                            $statusClass = match($application->status) {
                                'submitted' => 'badge bg-primary',
                                'reviewed' => 'badge bg-warning text-dark',
                                'interviewed' => 'badge bg-info text-dark',
                                'hired' => 'badge bg-success',
                                'rejected' => 'badge bg-danger',
                                default => 'badge bg-secondary',
                            };
                        @endphp
                        <span class="{{ $statusClass }}">{{ ucfirst($application->status) }}</span>
                    </p>
                    <p class="mb-2"><strong>Applied:</strong> {{ $application->created_at->format('M d, Y') }}</p>
                </div>

                <!-- Cover Letter -->
                <h4 class="text-dark fw-semibold mb-3">Cover Letter</h4>
                <div class="p-3 mb-4 bg-light rounded border">
                    {!! nl2br(e($application->cover_letter)) !!}
                </div>

                <!-- Resume Download -->
                <h4 class="text-dark fw-semibold mb-3">Resume</h4>
                <div class="mb-4">
                    <a href="{{ route('jobs.downloadResume', $application) }}" class="btn btn-primary">
                        <i class="fa-solid fa-download me-2"></i> Download Resume
                    </a>
                </div>

                <!-- Update Status Form (if authorized) -->
                @can('update', $application)
                <div class="mt-4">
                    <h4 class="text-dark fw-semibold mb-3">Update Status</h4>
                    <form action="{{ route('jobs.applications.update', [$job, $application]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="submitted" {{ $application->status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="reviewed" {{ $application->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                    <option value="interviewed" {{ $application->status === 'interviewed' ? 'selected' : '' }}>Interviewed</option>
                                    <option value="hired" {{ $application->status === 'hired' ? 'selected' : '' }}>Hired</option>
                                    <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes', $application->notes) }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-check me-2"></i> Update Status
                        </button>
                    </form>
                </div>
                @endcan

            </div>
        </div>
    </div>
</div>
@endsection