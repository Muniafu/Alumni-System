<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <input type="text" wire:model.live="search" class="form-control" placeholder="Search jobs...">
        </div>
        <div class="col-md-6">
            <button class="btn btn-outline-secondary" data-bs-toggle="collapse" href="#filtersCollapse">
                Filters
            </button>
        </div>
    </div>

    <div class="collapse mb-4" id="filtersCollapse">
        <div class="card card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Job Type</label>
                    <select wire:model.live="filters.job_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($jobTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <input type="text" wire:model.live="filters.location" class="form-control" placeholder="Any location">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salary Range</label>
                    <input type="text" wire:model.live="filters.salary_range" class="form-control" placeholder="e.g. 50k-70k">
                </div>
                <div class="col-12">
                    <button wire:click="resetFilters" class="btn btn-sm btn-outline-danger">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($jobs as $job)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $job->title }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">{{ $job->employer->name }}</h6>
                        <p class="card-text">{{ Str::limit($job->description, 200) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">{{ $job->job_type }}</span>
                            <span class="text-muted">{{ $job->location }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        @if(auth()->check() && auth()->user()->role === 'alumni')
                            @if($job->applications->contains('alumni_id', auth()->id()))
                                <button class="btn btn-sm btn-success" disabled>
                                    Applied
                                </button>
                            @else
                                <button wire:click="apply({{ $job->id }})" class="btn btn-sm btn-primary">
                                    Apply Now
                                </button>
                            @endif
                        @elseif(!auth()->check())
                            <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                                Login to Apply
                            </a>
                        @endif
                        <small class="text-muted float-end">
                            Deadline: {{ $job->application_deadline->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            {{ $jobs->links() }}
        </div>
    </div>
</div>