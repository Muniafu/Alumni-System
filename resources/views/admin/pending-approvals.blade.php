@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-4 text-dark">
        {{ __('Pending Approvals') }}
    </h2>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="type" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Empty State --}}
            @if($users->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-person-x display-4 text-muted mb-3"></i>
                    <p class="text-muted fs-5">No users pending approval.</p>
                </div>
            @else
                {{-- Bulk Action Form --}}
                <form method="POST" action="{{ route('admin.users.bulk-approval') }}">
                    @csrf

                    <div class="mb-3">
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-2">
                            <i class="bi bi-check-circle me-1"></i> Approve Selected
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Reject Selected
                        </button>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-approvals">
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-secondary">Name</th>
                                    <th class="text-uppercase small fw-semibold text-secondary">Email</th>
                                    <th class="text-uppercase small fw-semibold text-secondary">Role</th>
                                    <th class="text-uppercase small fw-semibold text-secondary">Student ID</th>
                                    <th class="text-uppercase small fw-semibold text-secondary">Graduation Year</th>
                                    <th class="text-uppercase small fw-semibold text-secondary">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        {{-- Checkbox --}}
                                        <td>
                                            <input type="checkbox" name="selected_users[]" value="{{ $user->id }}">
                                        </td>

                                        {{-- Name --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                            </div>
                                        </td>

                                        {{-- Email --}}
                                        <td class="text-muted">{{ $user->email }}</td>

                                        {{-- Role --}}
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                {{ ucfirst(optional($user->roles->first())->name ?? 'N/A') }}
                                            </span>
                                        </td>

                                        {{-- Student ID --}}
                                        <td class="text-muted">{{ $user->student_id }}</td>

                                        {{-- Graduation Year --}}
                                        <td class="text-muted">{{ $user->graduation_year }}</td>

                                        {{-- Individual Actions --}}
                                        <td>
                                            <div class="d-flex">
                                                {{-- Approve --}}
                                                <form action="{{ route('admin.approve-user', $user) }}" method="POST" class="me-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-lg me-1"></i> Approve
                                                    </button>
                                                </form>

                                                {{-- Reject --}}
                                                <form action="{{ route('admin.reject-user', $user) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-x-lg me-1"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Bulk Select Script --}}
<script>
    document.getElementById('select-all-approvals').addEventListener('click', function(e) {
        document.querySelectorAll('input[name="selected_users[]"]').forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endsection