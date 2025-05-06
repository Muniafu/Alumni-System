<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category'
    ];

    public function alumni()
    {
        return $this->belongsToMany(AlumniProfile::class, 'alumni_profile_skill');
    }
    
}
