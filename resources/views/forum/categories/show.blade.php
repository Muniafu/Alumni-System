@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        {{ $category->name }} - Threads
    </h2>
    @can('create', App\Models\ForumThread::class)
        <a href="{{ route('forum.threads.create', $category) }}"
           class="btn btn-primary btn-sm shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> New Thread
        </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row mt-4 g-4">
    @forelse($threads as $thread)
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <!-- Thread Info -->
                    <div>
                        <a href="{{ route('forum.threads.show', $thread) }}"
                           class="h5 text-primary text-decoration-none">
                            @if($thread->is_pinned)
                                📌 {{ $thread->title }}
                            @else
                                {{ $thread->title }}
                            @endif
                        </a>
                        <p class="text-muted small mb-0 mt-1">
                            Started by <span class="fw-medium">{{ $thread->author?->name ?? 'Former member' }}</span> •
                            {{ $thread->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <!-- Thread Stats -->
                    <div class="text-end">
                        <span class="badge bg-secondary mb-1">
                            {{ $thread->posts_count }} {{ Str::plural('reply', $thread->posts_count) }}
                        </span>
                        @if($thread->latestPost)
                            <div class="text-muted small">
                                Last reply {{ $thread->latestPost->created_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info shadow-sm">
                No threads found in this category.
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $threads->links('pagination::bootstrap-5') }}
</div>
@endsection
