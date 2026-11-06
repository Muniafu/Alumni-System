@extends('layouts.app')

@section('header')
<h2 class="fw-semibold fs-4 text-dark">
    <i class="bi bi-pencil-square text-warning me-2"></i> {{ __('Edit User') }}
</h2>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.user.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Basic Information --}}
                    <div class="col-md-6">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-person-lines-fill me-2"></i> Basic Information
                        </h5>

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Name</label>
                            <input id="name" type="text" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" type="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- New Password (optional) --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                            <input id="password" type="password" name="password" autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   class="form-control @error('password_confirmation') is-invalid @enderror">
                            @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Role (null-safe: a user without a role used to crash this page) --}}
                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Role</label>
                            <select id="role" name="role"
                                    class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Student/Alumni Information --}}
                    <div class="col-md-6" id="studentFields" style="display:none;">
                        <h5 class="fw-bold text-success mb-3">
                            <i class="bi bi-mortarboard-fill me-2"></i> Student / Alumni Information
                        </h5>

                        {{-- Student ID (read-only: generated server-side) --}}
                        <div class="mb-3">
                            <label for="student_id" class="form-label fw-semibold">Student ID</label>
                            <input id="student_id" type="text" class="form-control" value="{{ $user->student_id ?? 'N/A' }}" readonly>
                            <div class="form-text">Student IDs are generated automatically and cannot be edited.</div>
                        </div>

                        {{-- Graduation Year --}}
                        <div class="mb-3">
                            <label for="graduation_year" class="form-label fw-semibold">Graduation Year</label>
                            <select id="graduation_year" name="graduation_year"
                                    class="form-select @error('graduation_year') is-invalid @enderror">
                                <option value="">Select Graduation Year</option>
                                @foreach($graduationYears as $year)
                                    <option value="{{ $year }}"
                                        {{ old('graduation_year', $user->graduation_year) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('graduation_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Program --}}
                        <div class="mb-3">
                            <label for="program" class="form-label fw-semibold">Program</label>
                            <select id="program" name="program" class="form-select @error('program') is-invalid @enderror">
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program }}"
                                        {{ old('program', $user->program) == $program ? 'selected' : '' }}>
                                        {{ $program }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.user-management') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-warning shadow-sm">
                        <i class="bi bi-save2 me-1"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleRoleFields() {
        const role = document.getElementById('role').value;
        document.getElementById('studentFields').style.display =
            (role === 'student' || role === 'alumni') ? 'block' : 'none';
    }
    document.getElementById('role').addEventListener('change', toggleRoleFields);
    toggleRoleFields(); // run on page load
</script>
@endpush
