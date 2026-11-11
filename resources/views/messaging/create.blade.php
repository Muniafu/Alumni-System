@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-plus me-2"></i> New Conversation
    </h2>
</div>
@endsection

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-lg-8">

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('conversations.store') }}">
                    @csrf

                    <!-- Recipients -->
                    <div class="mb-3">
                        <label for="recipients" class="form-label fw-semibold">Recipients</label>
                        <select name="recipients[]" id="recipients" multiple class="form-select" aria-label="Select recipients">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                        {{ in_array($user->id, $preselected) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select one or more recipients for this conversation.</small>
                    </div>

                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label fw-semibold">Subject (optional)</label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter a subject for your conversation">
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label for="message" class="form-label fw-semibold">Message</label>
                        <textarea name="message" id="message" rows="4" class="form-control" placeholder="Type your message here..." required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Start Conversation
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Initialize Select2 for multi-select recipients — only if the
    // plugin is actually loaded; otherwise the native multi-select works.
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery('#recipients').select2({
                placeholder: "Select recipients",
                width: '100%'
            });
        }
    });
</script>
@endpush
@endsection
