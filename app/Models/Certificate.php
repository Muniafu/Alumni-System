<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certificate extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'alumni_profile_id',
        'name',
        'path',
        'uploaded_at'
    ];

    public function alumniProfile()
    {
        return $this->belongsTo(AlumniProfile::class);
    }
    
}