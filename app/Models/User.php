<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable /** implements MustVerifyEmail*/
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_completed',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed' => 'boolean',
            'password' => 'hashed',
        ];
    }    

    public const ROLES = [
        'admin' => 'Admin',
        'alumni' => 'Alumni',
        'employer' => 'Employer'
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAlumni()
    {
        return $this->role === 'alumni';
    }

    public function isEmployer()
    {
        return $this->role === 'employer';
    }

    public function alumniProfile()
    {
        return $this->hasOne(AlumniProfile::class, 'user_id');
    }

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class);
    }    

    public function experiences()
    {
        return $this->hasManyThrough(Experience::class, AlumniProfile::class);
    }

    public function postedJobs()
    {
        return $this->hasMany(JobPosting::class, 'employer_id');
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'alumni_id');
    }

    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function attendedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_attendees')
            ->withPivot('status', 'feedback')
            ->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasManyThrough(Certificate::class, AlumniProfile::class);
    }
    
}
