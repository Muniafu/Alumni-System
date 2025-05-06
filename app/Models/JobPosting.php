<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobPosting extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'location',
        'employment_type',
        'salary_range',
        'application_deadline',
        'is_active'
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'is_active' => 'boolean'
    ];

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function applicants()
    {
        return $this->belongsToMany(User::class, 'job_applications', 'job_posting_id', 'alumni_id')
            ->withPivot('cover_letter', 'resume_path', 'status')
            ->withTimestamps();
    }
}