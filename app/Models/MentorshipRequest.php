<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'message',
        'response_message',
        'goal',
        'status',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    /**
     * The Mentorship created when this request is accepted.
     * (Blades already referenced $request->mentorship — the relation
     * simply never existed.)
     */
    public function mentorship()
    {
        return $this->hasOne(Mentorship::class, 'mentorship_request_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForMentor($query, $userId)
    {
        return $query->where('mentor_id', $userId);
    }

    public function scopeForMentee($query, $userId)
    {
        return $query->where('mentee_id', $userId);
    }
}
