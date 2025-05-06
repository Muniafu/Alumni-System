<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Experience extends Model
{    
    use HasFactory;

    protected $fillable = [
        'alumni_profile_id',
        'type',
        'title',
        'organization',
        'description',
        'start_date',
        'end_date',
        'is_current'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean'
    ];

    public function alumniProfile()
    {
        return $this->belongsTo(AlumniProfile::class);
    }

}
