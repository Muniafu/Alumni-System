<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An alumni's mentor signup: what they offer and why they mentor.
 * Only alumni with a row here appear on "Find Mentors".
 */
class MentorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'expertise',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
