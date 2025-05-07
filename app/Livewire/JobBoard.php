<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JobPosting;
use Livewire\WithPagination;

class JobBoard extends Component
{
    use WithPagination;

    public $search = '';
    public $location = '';
    public $minSalary = '';
    public $employmentType = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'location' => ['except' => ''],
        'minSalary' => ['except' => ''],
        'employmentType' => ['except' => '']
    ];

    public function render()
    {
        $jobs = JobPosting::query()
            ->where('status', 'active')
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->location, fn($q) => $q->where('location', 'like', "%{$this->location}%"))
            ->when($this->minSalary, fn($q) => $q->where('salary_range', '>=', $this->minSalary))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.job-board', compact('jobs'));
    }

    public function apply($jobId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $job = JobPosting::findOrFail($jobId);

        if ($job->applications()->where('alumni_id', auth()->id())->exists()) {
            $this->addError('application', 'You have already applied to this job.');
            return;
        }

        $job->applications()->create([
            'alumni_id' => auth()->id(),
            'status' => 'pending',
            'applied_at' => now()
        ]);

        session()->flash('message', 'Application submitted successfully!');
    }

    public function resetFilters()
    {
        $this->reset('filters');
    }
}