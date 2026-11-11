<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\MentorshipStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class Mentorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'mentorship_request_id',
        'status',
        'goal',
        'notes',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'status' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function request()
    {
        return $this->belongsTo(MentorshipRequest::class, 'mentorship_request_id');
    }

    /**
     * Single source of truth for status-change notifications:
     * mentor, mentee and admins are each notified exactly once, here.
     * (The controller no longer sends duplicate notifications.)
     */
    protected static function booted()
    {
        static::updated(function ($mentorship) {
            if ($mentorship->wasChanged('status')) {
                $newStatus = $mentorship->status;

                // Null-safe: a soft-deleted mentor or mentee must not
                // crash the status update for the surviving member.
                $recipients = collect([$mentorship->mentor, $mentorship->mentee])
                    ->filter()
                    ->merge(User::role('admin')->get());

                Notification::send(
                    $recipients->unique('id')->values(),
                    new MentorshipStatusChangedNotification($mentorship, $newStatus)
                );
            }
        });
    }
}
