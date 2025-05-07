<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AlumniProfile;
use Livewire\WithPagination;

class DirectorySearch extends Component
{
    use WithPagination;

    public $search = '';
    public $filters = [
        'graduation_year' => '',
        'industry' => '',
        'skills' => []
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => []]
    ];

    public function render()
    {
        $alumni = AlumniProfile::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('location', 'like', '%'.$this->search.'%');
                })
                ->orWhere('current_position', 'like', '%'.$this->search.'%');
            })
            ->when($this->filters['graduation_year'], function ($query, $year) {
                $query->where('graduation_year', $year);
            })
            ->when($this->filters['industry'], function ($query, $industry) {
                $query->where('industry', 'like', '%'.$industry.'%');
            })
            ->when($this->filters['skills'], function ($query, $skills) {
                foreach ($skills as $skill) {
                    $query->whereJsonContains('skills', $skill);
                }
            })
            ->orderBy('graduation_year', 'desc')
            ->paginate(10);

        return view('livewire.directory-search', [
            'alumni' => $alumni,
            'graduationYears' => AlumniProfile::distinct('graduation_year')->pluck('graduation_year'),
            'industries' => AlumniProfile::distinct('industry')->pluck('industry'),
            'skillsList' => $this->getSkillsList()
        ]);
    }

    protected function getSkillsList()
    {
        return AlumniProfile::all()
            ->flatMap(function ($profile) {
                return $profile->skills ?? [];
            })
            ->unique()
            ->values()
            ->toArray();
    }

    public function resetFilters()
    {
        $this->reset('filters');
    }
}