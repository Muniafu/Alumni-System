@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-users me-2"></i> My Mentorships
    </h2>
    <a href="{{ route('mentorship.find') }}" class="btn btn-primary">
        <i class="fa-solid fa-magnifying-glass me-1"></i> Find a Mentor
    </a>
</div>
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            @forelse($mentorships as $mentorship)
                <div class="card shadow-sm mb-4 border-0 hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    @if($mentorship->mentor_id === auth()->id())
                                        Mentoring {{ $mentorship->mentee?->name ?? 'Former member' }}
                                    @else
                                        Mentored by {{ $mentorship->mentor?->name ?? 'Former member' }}
                                    @endif
                                </h5>
                                <p class="text-secondary mb-2">
                                    Started {{ $mentorship->start_date?->format('M j, Y') }}
                                    @if($mentorship->end_date)
                                        • Ended {{ $mentorship->end_date->format('M j, Y') }}
                                    @endif
                                </p>
                                <span class="badge
                                    @if($mentorship->status === 'active') bg-success
                                    @elseif($mentorship->status === 'completed') bg-primary
                                    @else bg-info text-dark @endif
                                    text-capitalize mb-2">
                                    {{ $mentorship->status }}
                                </span>
                                <p class="text-secondary mb-0">{{ Str::limit($mentorship->goal ?? "", 120) }}</p>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('mentorship.show', $mentorship) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fa-solid fa-arrow-right me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <p class="text-secondary mb-3">You don't have any active mentorships.</p>
                    <a href="{{ route('mentorship.find') }}" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Find a Mentor
                    </a>
                </div>
            @endforelse

            @if($mentorships->isNotEmpty())
                <div class="d-flex justify-content-center mt-4">
                    {{ $mentorships->links('pagination::bootstrap-5') }}
                </div>
            @endif
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
