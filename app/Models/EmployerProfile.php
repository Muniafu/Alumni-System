<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployerProfile extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_size',
        'industry',
        'website',
        'contact_person',
        'contact_position',
        'phone',
        'about'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }

}
