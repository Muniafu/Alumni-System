@extends('layouts.app')

@section('header')
    <div class="bg-light border-bottom py-3 mb-4">
        <div class="container">
            <h2 class="fw-bold text-dark mb-0">{{ __('My Profile') }}</h2>
            <small class="text-muted">Overview of your account details</small>
        </div>
    </div>
@endsection

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profile Details</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{ $user->name }}</dd>

                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $user->email }}</dd>

                            <dt class="col-sm-4">Role</dt>
                            <dd class="col-sm-8">
                                {{ $user->roles->pluck('name')->map(fn ($r) => ucfirst($r))->join(', ') ?: 'N/A' }}
                            </dd>

                            <dt class="col-sm-4">Joined</dt>
                            <dd class="col-sm-8">{{ $user->created_at->format('d M Y') }}</dd>

                            @if ($profile)
                                @if ($profile->current_job)
                                    <dt class="col-sm-4">Current Job</dt>
                                    <dd class="col-sm-8">{{ $profile->current_job }}</dd>
                                @endif

                                @if ($profile->company)
                                    <dt class="col-sm-4">Company</dt>
                                    <dd class="col-sm-8">{{ $profile->company }}</dd>
                                @endif

                                <dt class="col-sm-4">Profile Completion</dt>
                                <dd class="col-sm-8">{{ $profile->profile_completion }}%</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
