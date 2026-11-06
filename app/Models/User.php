<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, Authorizable;

    /**
     * Mass-assignable fields only.
     *
     * SECURITY: is_approved, approved_by and approved_at are deliberately
     * NOT fillable. Approval state must only ever change through
     * approve() (called by authorized admin controllers) so that no
     * request payload can ever self-approve an account.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'student_id',
        'graduation_year',
        'program',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Fallback avatar URL. Blades reference $user->profile_photo_url
     * (e.g. the event attendee list) but no such attribute existed,
     * rendering broken images. Swap for real photo storage later.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
            . '&color=fff&background=0d6efd&size=128&bold=true';
    }

    public function forumThreads()
    {
        return $this->hasMany(ForumThread::class, 'user_id');
    }

    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class, 'user_id');
    }

    public function mentorships()
    {
        return $this->hasMany(Mentorship::class, 'mentor_id');
    }

    public function mentorshipRequests()
    {
        return $this->hasMany(Mentorship::class, 'mentee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_rsvps')
            ->withPivot('status', 'guests', 'notes')
            ->withTimestamps();
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Approval
    |--------------------------------------------------------------------------
    */

    /**
     * Approve this user. The single, sanctioned way to flip approval state.
     *
     * Uses forceFill() because is_approved/approved_by/approved_at are
     * intentionally excluded from $fillable.
     */
    public function approve(?self $approver = null): void
    {
        $this->forceFill([
            'is_approved' => true,
            'approved_by' => $approver?->id ?? auth()->id(),
            'approved_at' => now(),
        ])->save();
    }

    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

        public function mentorProfile()
    {
        return $this->hasOne(MentorProfile::class);
    }
}
