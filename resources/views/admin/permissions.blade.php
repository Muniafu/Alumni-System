@extends('layouts.app')

@section('header')
<h2 class="fw-semibold fs-4 text-dark">
    <i class="bi bi-shield-lock text-primary me-2"></i> {{ __('Permissions Management') }}
</h2>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <form action="{{ route('admin.permissions.update') }}" method="POST">
                @csrf

                {{-- Role Selection --}}
                <div class="mb-4">
                    <label for="role_id" class="form-label fw-semibold">
                        <i class="bi bi-person-badge me-1 text-primary"></i> Select Role
                    </label>
                    <select name="role_id" id="role_id"
                            class="form-select @error('role_id') is-invalid @enderror" required>
                        <option value="">-- Select a Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Permissions Grid --}}
                <h5 class="fw-semibold text-dark mb-3">
                    <i class="bi bi-key text-success me-2"></i> Assign Permissions
                </h5>
                <div class="row g-3">
                    @forelse($permissions as $permission)
                        <div class="col-md-4">
                            <div class="form-check border p-2 rounded-2 shadow-sm h-100">
                                <input id="permission-{{ $permission->id }}"
                                       name="permissions[]"
                                       type="checkbox"
                                       value="{{ $permission->id }}"
                                       class="form-check-input">
                                <label for="permission-{{ $permission->id }}"
                                       class="form-check-label fw-medium text-muted">
                                    {{ ucfirst($permission->name) }}
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted fst-italic">No permissions found.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Submit Button --}}
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-success shadow-sm px-4">
                        <i class="bi bi-shield-lock-fill me-2"></i> Update Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('role_id').addEventListener('change', function() {
    const roleId = this.value;
    if (!roleId) return;

    // Reset all checkboxes first
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);

    fetch(`/admin/permissions/${roleId}`)
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            if (!data.permissions || !Array.isArray(data.permissions)) return;
            data.permissions.forEach(permissionId => {
                const checkbox = document.getElementById(`permission-${permissionId}`);
                if (checkbox) checkbox.checked = true;
            });
        })
        .catch(() => alert('Failed to fetch role permissions. Please try again.'));
});
</script>
@endpush
