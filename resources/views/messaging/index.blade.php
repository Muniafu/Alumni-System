@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-comments me-2"></i> My Conversations
    </h2>
    <a href="{{ route('conversations.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> New Conversation
    </a>
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

<div class="row justify-content-center py-4">
    <div class="col-lg-8">

        <div class="card shadow-sm border-0">
            <div class="card-body">

                @if($conversations->count())
                    <div class="list-group">
                        @foreach($conversations as $conversation)
                            <a href="{{ route('conversations.show', $conversation) }}"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-start mb-2 shadow-sm rounded hover-shadow">

                                <div class="ms-2 me-auto">
                                    <div class="fw-semibold">
                                        @foreach($conversation->otherUsers as $user)
                                            {{ $user->name }}@if(!$loop->last), @endif
                                        @endforeach
                                        @if($conversation->isUnread)
                                            <span class="badge bg-primary rounded-pill ms-1">New</span>
                                        @endif
                                    </div>
                                    <p class="mb-1 text-truncate text-muted">
                                        {{ $conversation->latestMessage->body ?? 'No messages yet.' }}
                                    </p>
                                </div>

                                @if($conversation->latestMessage)
                                    <small class="text-muted">
                                        {{ $conversation->latestMessage->created_at->diffForHumans() }}
                                    </small>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-4">You have no conversations yet.</p>
                @endif

                <div class="mt-3">
                    {{ $conversations->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Optional: Add hover effect for list items
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.addEventListener('mouseenter', () => item.classList.add('shadow-lg'));
        item.addEventListener('mouseleave', () => item.classList.remove('shadow-lg'));
    });
</script>
@endpush
@endsection
