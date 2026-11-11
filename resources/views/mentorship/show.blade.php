@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-handshake-angle me-2"></i> Mentorship Details
    </h2>
</div>
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 class="fw-bold">
                        @if($mentorship->mentor_id === auth()->id())
                            Mentoring {{ $mentorship->mentee?->name ?? 'Former member' }}
                        @else
                            Mentored by {{ $mentorship->mentor?->name ?? 'Former member' }}
                        @endif
                    </h3>
                    <p class="text-muted small">
                        Started {{ $mentorship->start_date?->format('M j, Y') }}
                        @if($mentorship->end_date)
                            • Ended {{ $mentorship->end_date->format('M j, Y') }}
                        @endif
                    </p>
                </div>
                <span class="badge
                    @if($mentorship->status === 'active') bg-success
                    @elseif($mentorship->status === 'completed') bg-primary
                    @else bg-secondary @endif
                    fs-6 py-2 px-3 text-light">
                    {{ ucfirst($mentorship->status) }}
                </span>
            </div>

            <!-- Mentor & Mentee Details -->
            <div class="row g-4 mb-4">
                <!-- Mentor -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-start">
                            <div class="me-3">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                    <span class="text-light fs-5">{{ strtoupper(substr($mentorship->mentor?->name ?? 'Former member', 0, 1)) }}</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-medium mb-1">{{ $mentorship->mentor?->name ?? 'Former member' }}</h5>
                                <p class="text-muted mb-0 small">
                                    {{ $mentorship->mentor?->profile->current_job ?? 'No job specified' }}
                                </p>
                                <p class="text-muted mb-0 small">
                                    {{ $mentorship->mentor?->profile->company ?? 'No company specified' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mentee -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-start">
                            <div class="me-3">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                    <span class="text-light fs-5">{{ strtoupper(substr($mentorship->mentee?->name ?? 'Former member', 0, 1)) }}</span>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-medium mb-1">{{ $mentorship->mentee?->name ?? 'Former member' }}</h5>
                                <p class="text-muted mb-0 small">
                                    Class of {{ $mentorship->mentee?->graduation_year ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-0 small">
                                    {{ $mentorship->mentee?->program ?? 'No program specified' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentorship Goal -->
            <div class="mb-4">
                <h5 class="fw-semibold mb-2">Mentorship Goal</h5>
                <p class="text-secondary" style="white-space: pre-line;">{{ $mentorship->goal ?? "No goal specified" }}</p>
            </div>

            <!-- Update Status Form -->
            @if($mentorship->mentor_id === auth()->id() || $mentorship->mentee_id === auth()->id())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Update Mentorship Status</h5>
                    <form action="{{ route('mentorship.update', $mentorship) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="active" @if($mentorship->status === 'active') selected @endif>Active</option>
                                    <option value="completed" @if($mentorship->status === 'completed') selected @endif>Completed</option>
                                    <option value="cancelled" @if($mentorship->status === 'cancelled') selected @endif>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $mentorship->notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
