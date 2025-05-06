@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Complete Your Profile') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.complete') }}">
                        @csrf

                        @if(auth()->user()->isAlumni())
                            <!-- Alumni Fields -->
                            <div class="mb-3">
                                <label for="graduation_year" class="form-label">{{ __('Graduation Year') }}</label>
                                <input id="graduation_year" type="number" class="form-control @error('graduation_year') is-invalid @enderror" 
                                    name="graduation_year" value="{{ old('graduation_year') }}" required>
                                @error('graduation_year')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- More alumni fields... -->
                        
                        @elseif(auth()->user()->isEmployer())
                            <!-- Employer Fields -->
                            <div class="mb-3">
                                <label for="company_name" class="form-label">{{ __('Company Name') }}</label>
                                <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                    name="company_name" value="{{ old('company_name') }}" required>
                                @error('company_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- More employer fields... -->
                        @endif

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Complete Profile') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection