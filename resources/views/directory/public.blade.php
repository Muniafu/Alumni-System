@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-users me-2"></i> Alumni & Student Directory
    </h2>
    <span class="badge bg-secondary">Public preview — sign in for the full directory</span>
</div>
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('public.directory') }}" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by name, program, job or company..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($users->isEmpty())
        <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i>No members found.
        </div>
    @else
        <div class="row">
            @foreach($users as $user)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            @php
                                $role = $user->roles->pluck('name')->first();
                            @endphp
                            <span class="badge {{ $role === 'alumni' ? 'bg-primary' : 'bg-success' }} mb-2">
                                {{ ucfirst($role ?? 'Member') }}
                            </span>
                            <p class="text-secondary mb-1">{{ $user->program ?? '—' }}</p>
                            <p class="text-secondary mb-0 small">Class of {{ $user->graduation_year ?? '—' }}</p>
                            @if($user->profile && ($user->profile->current_job || $user->profile->company))
                                <p class="text-secondary mb-0 small">
                                    {{ $user->profile->current_job ?? '' }}@if($user->profile->current_job && $user->profile->company) @ {{ $user->profile->company }}@else{{ $user->profile->company ?? '' }}@endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
