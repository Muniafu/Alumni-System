<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends Model
{    
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'alumni_id',
        'cover_letter',
        'resume_path',
        'status'
    ];

    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function alumni()
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }
}
