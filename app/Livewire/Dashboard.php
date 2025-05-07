<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Event;
use App\Models\JobApplication;
use App\Models\AlumniProfile;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]

class Dashboard extends Component
{
    public $selectedYear;
    public $years;

    public function mount()
    {
        $this->years = AlumniProfile::distinct('graduation_year')
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');
    }

    public function render()
    {
        $stats = [
            'total_alumni' => User::where('role', 'alumni')->count(),
            'total_employers' => User::where('role', 'employer')->count(),
            'active_jobs' => JobApplication::where('status', 'pending')->count(),
            'upcoming_events' => Event::where('start_time', '>', now())->count(),
        ];

        $alumniByYear = AlumniProfile::when($this->selectedYear, function ($query) {
                return $query->where('graduation_year', $this->selectedYear);
            })
            ->with('user')
            ->orderBy('graduation_year', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'recentEvents' => Event::orderBy('start_time', 'desc')->limit(5)->get(),
            'alumniByYear' => $alumniByYear,
            'chartData' => $this->prepareChartData(),
        ]);
    }

    protected function prepareChartData()
    {
        $data = AlumniProfile::selectRaw('graduation_year, count(*) as count')
            ->groupBy('graduation_year')
            ->orderBy('graduation_year')
            ->get();

        return [
            'labels' => $data->pluck('graduation_year'),
            'values' => $data->pluck('count'),
        ];
    }
}
