@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-comments me-2"></i> Conversation
    </h2>
    <div class="d-flex gap-2">
        <a href="{{ route('conversations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <form method="POST"
              action="{{ route('conversations.destroy', $conversation) }}"
              onsubmit="return confirm('Leave this conversation?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Leave
            </button>
        </form>
    </div>
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

        <!-- Conversation Header -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-semibold mb-1">
                    @foreach($conversation->otherUsers as $user)
                        {{ $user->name }}@if(!$loop->last), @endif
                    @endforeach
                </h5>
                @if($conversation->subject)
                    <p class="text-muted mb-0">{{ $conversation->subject }}</p>
                @endif
            </div>
        </div>

        <!-- Messages Container -->
        <div class="card shadow-sm border-0 mb-4" style="height: 500px; overflow-y: auto;">
            <div class="card-body d-flex flex-column gap-3">
                {{-- Page 1 holds the NEWEST messages; reverse the page so the
                      chat reads top-to-bottom and the newest sit at the
                      bottom, where the container auto-scrolls. --}}
                @forelse($messages->reverse() as $message)
                    <div class="d-flex {{ $message->sender?->id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="p-3 rounded shadow-sm
                            {{ $message->sender?->id === auth()->id() ? 'bg-primary text-white' : 'bg-light text-dark' }}"
                            style="max-width: 75%;">
                            <div class="d-flex align-items-center mb-1">
                                <span class="fw-bold">{{ $message->sender->name ?? 'Former member' }}</span>
                                <small class="text-muted ms-2">{{ $message->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0">{{ $message->body }}</p>
                            @can('delete', $message)
                                <form method="POST"
                                      action="{{ route('conversations.messages.destroy', [$conversation, $message]) }}"
                                      onsubmit="return confirm('Delete this message?');"
                                      class="mt-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-link btn-sm p-0 {{ $message->sender->id === auth()->id() ? 'text-white-50' : 'text-danger' }}">
                                        <i class="fa-solid fa-trash-can me-1"></i>Delete
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted mt-3">No messages yet. Start the conversation!</p>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div class="mb-3">
            {{ $messages->links('pagination::bootstrap-5') }}
        </div>

        <!-- Send Message Form -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- Route name fixed: the nested resource is registered as
                      conversations.messages.store, NOT messages.store. --}}
                <form method="POST" action="{{ route('conversations.messages.store', $conversation) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="body" class="form-label visually-hidden">Message</label>
                        <textarea id="body" name="body" rows="3" class="form-control" placeholder="Type your message here..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Auto-scroll to the bottom of messages
    const messagesContainer = document.querySelector('.card-body.d-flex.flex-column');
    if(messagesContainer){
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
</script>
@endpush
@endsection
