<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Event;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Charts\ColumnChart;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $selectedYear;
    public $years;

    public function mount()
    {
        $this->years = User::whereHas('alumniProfile')
            ->distinct('alumni_profiles.graduation_year')
            ->join('alumni_profiles', 'users.id', '=', 'alumni_profiles.user_id')
            ->orderBy('alumni_profiles.graduation_year', 'desc')
            ->pluck('alumni_profiles.graduation_year');
    }

    public function render()
    {
        $user = Auth::user();
        
        return view('livewire.dashboard', [
            'role' => $user->role,
            'metrics' => $this->getRoleSpecificMetrics($user),
            'chart' => $this->buildChart(),
            'recentData' => $this->getRecentData($user)
        ]);
    }

    protected function getRoleSpecificMetrics($user)
    {
        switch ($user->role) {
            case 'admin':
                return [
                    'total_users' => User::count(),
                    'total_alumni' => User::where('role', 'alumni')->count(),
                    'total_employers' => User::where('role', 'employer')->count(),
                    'active_jobs' => JobPosting::where('is_active', true)->count()
                ];
                
            case 'employer':
                return [
                    'posted_jobs' => $user->jobPosts()->count(),
                    'total_applications' => JobApplication::whereIn('job_posting_id', $user->jobPosts()->pluck('id'))->count(),
                    'open_positions' => $user->jobPosts()->active()->count(),
                    'upcoming_interviews' => 0 // Implement based on your logic
                ];

            case 'alumni':
                return [
                    'profile_completeness' => optional($user->alumniProfile)->completeness_score ?? 0,
                    'connections' => $user->connections()->count(),
                    'applications' => $user->jobApplications()->count(),
                    'upcoming_events' => Event::whereHas('attendees', fn($q) => $q->where('user_id', $user->id))
                        ->upcoming()
                        ->count()
                ];

            default: return [];
        }
    }

    protected function buildChart()
    {
        $chart = (new ColumnChart())
            ->setTitle('Alumni Distribution')
            ->setAnimated(true)
            ->withOnColumnClickEventName('chartClicked');

        // Fixed syntax error: added = sign and corrected query
        $records = User::where('role', 'alumni')
            ->when($this->selectedYear, fn($q) => $q->whereHas('alumniProfile', 
                fn($q) => $q->where('graduation_year', $this->selectedYear)
            ))
            ->join('alumni_profiles', 'users.id', '=', 'alumni_profiles.user_id')
            ->leftJoin('job_applications', 'users.id', '=', 'job_applications.alumni_id')
            ->selectRaw('alumni_profiles.graduation_year, 
                        COUNT(DISTINCT users.id) as total, 
                        SUM(CASE WHEN job_applications.status = "hired" THEN 1 ELSE 0 END) as hired')
            ->groupBy('alumni_profiles.graduation_year')
            ->get();

        foreach ($records as $record) {
            $chart->addColumn($record->graduation_year, $record->total, '#3490dc');
            $chart->addColumn($record->graduation_year.' Hired', $record->hired, '#38c172');
        }

        return $chart;
    }

    protected function getRecentData($user)
    {
        switch ($user->role) {
            case 'admin':
                return [
                    'events' => Event::latest()->limit(5)->get(),
                    'applications' => JobApplication::with(['jobPost', 'alumni'])->latest()->limit(5)->get()
                ];
                
            case 'employer':
                return [
                    'applications' => JobApplication::whereIn('job_posting_id', $user->jobPosts()->pluck('id'))
                        ->with(['jobPost', 'alumni'])
                        ->latest()
                        ->limit(5)
                        ->get(),
                    'jobs' => $user->jobPosts()->latest()->limit(5)->get()
                ];

            case 'alumni':
                return [
                    'events' => Event::whereHas('attendees', fn($q) => $q->where('user_id', $user->id))
                        ->upcoming()
                        ->limit(5)
                        ->get(),
                    'jobs' => JobPosting::recommendedFor($user)->limit(5)->get()
                ];

            default: return [];
        }
    }
}