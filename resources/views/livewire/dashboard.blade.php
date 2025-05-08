<div class="container-fluid py-4">
    <!-- Role-based Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                @switch($role)
                    @case('admin') Administrator Dashboard @break
                    @case('employer') Employer Portal @break
                    @case('alumni') My Alumni Dashboard @break
                @endswitch
            </h2>
            
            @if($role === 'admin')
            <div class="row mt-3">
                <div class="col-md-3">
                    <select wire:model="selectedYear" class="form-select">
                        <option value="">All Graduation Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
        @foreach($metrics as $key => $value)
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase text-muted small mb-1">
                            {{ str_replace('_', ' ', $key) }}
                        </h5>
                        <h2 class="mb-0">{{ $value }}</h2>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Chart Section -->
    @if($role === 'admin')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <livewire:livewire-column-chart
                        :column-chart-model="$chart"
                    />
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity Section -->
    <div class="row g-4">
        @foreach($recentData as $type => $items)
            @if($items->isNotEmpty())
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Recent {{ ucfirst($type) }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($items as $item)
                                @include("dashboard.recent-{$type}-item", ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>