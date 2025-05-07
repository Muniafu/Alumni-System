<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <input type="text" wire:model.live="search" class="form-control" placeholder="Search alumni...">
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
                    <label class="form-label">Graduation Year</label>
                    <select wire:model.live="filters.graduation_year" class="form-select">
                        <option value="">All Years</option>
                        @foreach($graduationYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Industry</label>
                    <select wire:model.live="filters.industry" class="form-select">
                        <option value="">All Industries</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry }}">{{ $industry }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Skills</label>
                    <select wire:model.live="filters.skills" class="form-select" multiple>
                        @foreach($skillsList as $skill)
                            <option value="{{ $skill }}">{{ $skill }}</option>
                        @endforeach
                    </select>
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
        @foreach($alumni as $profile)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($profile->user->name) }}&background=random" 
                                     class="rounded-circle" width="50" alt="Profile">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="card-title mb-0">{{ $profile->user->name }}</h5>
                                <p class="text-muted mb-0">{{ $profile->current_position }}</p>
                            </div>
                        </div>
                        <p class="card-text">{{ Str::limit($profile->bio, 150) }}</p>
                        <div class="mb-2">
                            @foreach(array_slice($profile->skills, 0, 5) as $skill)
                                <span class="badge bg-light text-dark me-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ $profile->graduation_year }}</span>
                            <span class="text-muted">{{ $profile->user->location }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="#" class="btn btn-sm btn-outline-primary">View Profile</a>
                        <a href="#" class="btn btn-sm btn-outline-secondary ms-2">Connect</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            {{ $alumni->links() }}
        </div>
    </div>
</div>