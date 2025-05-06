<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlumniProfile extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'user_id',
        'graduation_year',
        'current_position',
        'phone',
        'bio',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'skills'
    ];

    protected $casts = [
        'skills' => 'array',
        'graduation_year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }

}
