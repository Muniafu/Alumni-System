<div class="container-fluid py-4" x-data>
    <div class="row mb-4">
        <div class="col-md-3">
            <select wire:model.live="selectedYear" class="form-select">
                <option value="">All Graduation Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Alumni</h5>
                    <h2 class="display-4">{{ $stats['total_alumni'] }}</h2>
                </div>
            </div>
        </div>
        <!-- Repeat for other stats -->
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Alumni Distribution</h5>
                    <div wire:ignore>
                        <canvas id="graduationChart" x-init="
                            new Chart($el, {
                                type: 'bar',
                                data: {
                                    labels: {{ json_encode($chartData['labels']) }},
                                    datasets: [{
                                        label: 'Alumni by Graduation Year',
                                        data: {{ json_encode($chartData['values']) }},
                                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: { responsive: true }
                            });
                        "></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Recent Events</h5>
                    <div class="list-group">
                        @foreach($recentEvents as $event)
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $event->title }}</h6>
                                    <small>{{ $event->start_time->diffForHumans() }}</small>
                                </div>
                                <small>{{ $event->location }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>