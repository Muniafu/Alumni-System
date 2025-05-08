<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event as IcalEvent;

class Event extends Model
{    
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'online_link',
        'is_online',
        'capacity',
        'image_path'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_online' => 'boolean'
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_attendees')
            ->withPivot('status', 'feedback')
            ->withTimestamps();
    }

    public function toICalendar()
    {
        return Calendar::create($this->title)
            ->event(IcalEvent::create($this->title)
                ->startsAt($this->start_time)
                ->endsAt($this->end_time)
                ->address($this->location)
                ->description($this->description)
            )
            ->get();
    }

    public function scopeUpcoming(Builder $query)
    {
        return $query->where('start_time', '>', now())
            ->orderBy('start_time');
    }

}
