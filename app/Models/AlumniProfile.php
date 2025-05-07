<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class AlumniProfile extends Model
{
    
    use HasFactory, Searchable;

    protected $fillable = [
        'user_id',
        'graduation_year',
        'current_position',
        'industry',
        'phone',
        'bio',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'skills',
        'privacy_settings'
    ];

    protected $casts = [
        'skills' => 'array',
        'graduation_year' => 'integer',
        'privacy_settings' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'alumni_profile_skill');
    }
    
    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function jobApplications()
    {
        return $this->hasManyThrough(JobApplication::class, User::class);
    }

        public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
    
    public function toSearchableArray()
    {
        return [
            'graduation_year' => $this->graduation_year,
            'current_position' => $this->current_position,
            'industry' => $this->industry,
            'skills' => $this->skills,
            'user' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'location' => $this->user->location
            ]
        ];
    }

}