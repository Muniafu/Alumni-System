@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold fs-4 text-dark">
        {{ __('User Management') }}
    </h2>
    <a href="{{ route('admin.user.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-person-plus me-2"></i> Create New User
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('admin.user-management') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name/email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Approved (default)</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </div>
    </form>

    {{-- Bulk action form: separate element; checkboxes attach via form="" --}}
    <form method="POST" action="{{ route('admin.users.bulk-action') }}" id="bulkActionForm">
        @csrf
        <div class="mb-2">
            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete selected users?')">
                <i class="bi bi-trash me-1"></i> Bulk Delete
            </button>
            <button type="submit" name="action" value="restore" class="btn btn-sm btn-success">
                <i class="bi bi-arrow-clockwise me-1"></i> Restore Selected
            </button>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="{{ $user->trashed() ? 'table-danger' : '' }}">
                                <td>
                                    <input type="checkbox" name="selected_users[]" value="{{ $user->id }}" form="bulkActionForm">
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ optional($user->roles->first())->name ?? 'N/A' }}</td>
                                <td>
                                    @if($user->trashed())
                                        <span class="badge bg-danger">Deleted</span>
                                    @elseif($user->is_approved)
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Implicit binding excludes soft-deleted models,
                                         so View/Edit/Delete only render for live users. --}}
                                    @if($user->trashed())
                                        <form action="{{ route('admin.user.restore', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.user.show', $user) }}" class="btn btn-sm btn-outline-info">View</a>
                                        <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('admin.user.delete', $user) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No users match this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

{{-- JS for select all --}}
<script>
document.getElementById('select-all').addEventListener('click', function(e) {
    const checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
    checkboxes.forEach(cb => cb.checked = e.target.checked);
});
</script>
@endsection
