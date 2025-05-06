<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'google_id'
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
        return $this->hasOne(AlumniProfile::class);
    }

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class);
    }
}
