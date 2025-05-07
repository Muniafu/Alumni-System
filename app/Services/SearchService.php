<?php

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\JobPosting;

class SearchService
{
    public function searchAlumni($query, $filters = [])
    {
        return AlumniProfile::search($query)
            ->when(isset($filters['graduation_year']), function ($search) use ($filters) {
                $search->where('graduation_year', $filters['graduation_year']);
            })
            ->when(isset($filters['industry']), function ($search) use ($filters) {
                $search->where('industry', $filters['industry']);
            })
            ->when(isset($filters['skills']), function ($search) use ($filters) {
                foreach ($filters['skills'] as $skill) {
                    $search->where('skills', $skill);
                }
            })
            ->paginate(10);
    }

    public function searchJobs($query, $filters = [])
    {
        return JobPosting::search($query)
            ->when(isset($filters['job_type']), function ($search) use ($filters) {
                $search->where('job_type', $filters['job_type']);
            })
            ->when(isset($filters['location']), function ($search) use ($filters) {
                $search->where('location', $filters['location']);
            })
            ->where('is_active', true)
            ->where('application_deadline', '>', now())
            ->paginate(10);
    }
}