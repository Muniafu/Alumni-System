@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-envelope me-2"></i> Mentorship Requests
    </h2>
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
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-0">Manage Requests</h4>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="requestsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab">
                                Received
                                @if($receivedRequests->count() > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $receivedRequests->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab">
                                Sent
                                @if($sentRequests->count() > 0)
                                    <span class="badge bg-info text-light ms-1">{{ $sentRequests->count() }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="requestsTabContent">
                        <!-- Received Requests -->
                        <div class="tab-pane fade show active" id="received" role="tabpanel">
                            @if($receivedRequests->isEmpty())
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i> You have no received mentorship requests.
                                </div>
                            @else
                                @foreach($receivedRequests as $request)
                                    <div class="card mb-3 shadow-sm border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="mb-1">Request from {{ $request->mentee?->name ?? 'Former member' }}</h5>
                                                <span class="badge
                                                    @if($request->status === 'pending') bg-warning text-dark
                                                    @elseif($request->status === 'accepted') bg-success text-light
                                                    @else bg-danger text-light @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>
                                            <p class="text-muted mb-1"><strong>Goal:</strong> {{ $request->goal }}</p>
                                            <p class="mb-2">{{ $request->message }}</p>

                                            @if($request->status === 'pending')
                                                <form action="{{ route('mentorship.respond-to-request', $request) }}" method="POST">
                                                    @csrf
                                                    <div class="mb-2">
                                                        <label for="response-message-{{ $request->id }}" class="form-label">Response Message (Optional)</label>
                                                        <textarea class="form-control" id="response-message-{{ $request->id }}" name="message" rows="2"></textarea>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <button type="submit" name="response" value="accept" class="btn btn-success btn-sm">
                                                            Accept
                                                        </button>
                                                        <button type="submit" name="response" value="reject" class="btn btn-danger btn-sm">
                                                            Reject
                                                        </button>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                        <div class="card-footer text-muted">
                                            Requested {{ $request->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @endforeach
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $receivedRequests->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>

                        <!-- Sent Requests -->
                        <div class="tab-pane fade" id="sent" role="tabpanel">
                            @if($sentRequests->isEmpty())
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i> You have no sent mentorship requests.
                                </div>
                            @else
                                @foreach($sentRequests as $request)
                                    <div class="card mb-3 shadow-sm border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="mb-1">Request to {{ $request->mentor?->name ?? 'Former member' }}</h5>
                                                <span class="badge
                                                    @if($request->status === 'pending') bg-warning text-dark
                                                    @elseif($request->status === 'accepted') bg-success text-light
                                                    @else bg-danger text-light @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>
                                            <p class="text-muted mb-1"><strong>Goal:</strong> {{ $request->goal }}</p>
                                            <p class="mb-2">{{ $request->message }}</p>

                                            @if($request->status === 'accepted' && $request->mentorship)
                                                <a href="{{ route('mentorship.show', $request->mentorship) }}" class="btn btn-outline-primary btn-sm">
                                                    View Mentorship
                                                </a>
                                            @endif

                                            @if($request->status === 'rejected' && $request->response_message)
                                                <div class="alert alert-light">
                                                    <strong>Mentor's Response:</strong> {{ $request->response_message }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer text-muted">
                                            Sent {{ $request->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @endforeach
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $sentRequests->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            transition: box-shadow 0.3s ease;
        }
    </style>
</div>
@endsection
