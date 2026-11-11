<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRsvp extends Model
{
    protected $table = 'event_rsvps';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'guests',
        'notes'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
