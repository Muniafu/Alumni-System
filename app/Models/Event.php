<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\EventStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'start', 'end',
        'location', 'image', 'capacity', 'is_online',
        'meeting_url', 'user_id', 'status',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_online' => 'boolean',
        'status' => 'string',
    ];

    /** Relationships **/
    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_rsvps')
            ->withPivot('status', 'guests', 'notes')
            ->withTimestamps();
    }

    /** Accessors **/
    public function getAttendeesGoingAttribute()
    {
        return $this->attendees()->wherePivot('status', 'going')->count();
    }

    public function getAttendeesInterestedAttribute()
    {
        return $this->attendees()->wherePivot('status', 'interested')->count();
    }

    public function getAvailableSpotsAttribute()
    {
        return $this->capacity ? max(0, $this->capacity - $this->seatsTaken()) : null;
    }

    /** Helpers **/

    /**
     * Seats already taken: every "going" RSVP plus the guests they bring.
     */
    public function seatsTaken(): int
    {
        return (int) $this->rsvps()
            ->where('status', 'going')
            ->selectRaw('COUNT(*) + COALESCE(SUM(guests), 0) AS taken')
            ->value('taken');
    }

    public function isFull(): bool
    {
        return $this->capacity && ($this->seatsTaken() >= $this->capacity);
    }

    public function hasUserRsvped(User $user): bool
    {
        return $this->rsvps()->where('user_id', $user->id)->exists();
    }

    public function getUserRsvpStatus(User $user): ?string
    {
        $rsvp = $this->rsvps()->where('user_id', $user->id)->first();
        return $rsvp ? $rsvp->status : null;
    }

    public function isPast(): bool
    {
        return $this->end->lt(now());
    }

    public function isOngoing(): bool
    {
        return $this->start->lte(now()) && $this->end->gte(now());
    }

    /** Scopes **/
    public function scopeUpcoming($query)
    {
        // Include events already underway — they stay listed until they end.
        return $query->where('end', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end', '<', now());
    }

    public function scopeCurrent($query)
    {
        return $query->where('start', '<=', now())
                     ->where('end', '>=', now());
    }

    /**
     * Note: currently dormant — nothing updates `status` yet. Guarded against
     * a null organizer and excludes "not going" RSVPs from notifications.
     */
    protected static function booted()
    {
        static::updated(function ($event) {
            if (! $event->isDirty('status')) {
                return;
            }

            $oldStatus = $event->getOriginal('status');
            $newStatus = $event->status;

            if ($oldStatus === $newStatus) {
                return;
            }

            $notification = new EventStatusChangedNotification($event, $oldStatus, $newStatus);

            $attendees = $event->rsvps()
                ->whereIn('status', ['going', 'interested'])
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter()
                ->values();

            Notification::send($attendees, $notification);

            if ($event->organizer) {
                $event->organizer->notify($notification);
            }

            Notification::send(User::role('admin')->get(), $notification);
        });
    }
}
